<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_init_layout', 'exec_init_layout');
function exec_init_layout(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $rule = trim_incoming_filename($_POST['rule']);
     $group = trim_incoming_filename($_POST['group']);

     $doc = walk_the_doc($_POST['doc']);
     $doc = pigpack($doc);
     if(false == $doc){
          $message = esc_html(__('doc invalid', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $conf = [
          'post_type'=>'surveyprint_layout',
          'post_name'=>'layout_rule',
          'post_title'=>$group,
          'post_excerpt'=>$rule,
          'post_content'=>$doc,
          'tags_input'=>$tags_input
     ];

     $coll = init_layout($conf);
     $message = esc_html(__('layout inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll, 'term_id'=>$term_id));
}

add_action('admin_post_exec_get_layouts_by_group', 'exec_get_layouts_by_group');
function exec_get_layouts_by_group(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $group = trim_incoming_filename($_POST['group']);
     $coll = get_layouts_by_group($group);
     $message = esc_html(__('layouts loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_layout_by_group_and_rule', 'exec_get_layout_by_group_and_rule');
function exec_get_layout_by_group_and_rule(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $group = trim_incoming_filename($_POST['group']);
     $rule = trim_incoming_filename($_POST['rule']);
     $coll = get_layout_by_group_and_rule($group, $rule);
     $message = esc_html(__('layouts loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


/***********************************************************************
 svg layout import
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 --
 does work with svg plain documents
 as i copy and paste them into place from some wild svg documents
 as for to find the grey slots for to place the image assets
 and the hearties that is rendered above the masked image assets
 -----------------------------------------------------------------------
*/
add_action('admin_post_exec_import_layouts', 'exec_import_layouts');
function exec_import_layouts(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// reads layout svg fro the given rsloc
     $path = WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'default-layouts'.DIRECTORY_SEPARATOR.'svg';

     if(!is_dir($path)){
          $message = esc_html(__('nothing to import', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
          return true;
     }

     $dh = @opendir($path);

     $files = [];
     $targets = [];
     while(false !== $file = @readdir($dh)){
          $rsloc = $path.DIRECTORY_SEPARATOR.$file;
          $files[] = $rsloc;
          if(
               'image/svg' == mime_content_type($rsloc) ||
               'image/svg+xml' == mime_content_type($rsloc)

          ){
               $targets[] = $rsloc;
          }
     }

// parses svg documents into layout JSON collections

     $coll = [];
     $coll['ids'] = [];
     $coll['rules'] = [];
     $coll['paths'] = [];
     $coll['docs'] = [];

     foreach($targets as $svg_path){

          $doc = parse_layout_doc($svg_path);

          $coll['docs'][]= $doc;

          $rule = $doc['layout']['code'];
          $doc = pigpack($doc);
          $conf = [
               'post_type'=>'surveyprint_layout',
               'post_name'=>'layout_rule',
               'post_title'=>'default',
               'post_excerpt'=>$rule,
               'post_content'=>$doc,
               'tags_input'=>$tags_input
          ];

          $coll['ids'][]= init_layout($conf);
          $coll['rules'][]= $rule;
          $coll['paths'][]= $svg_path;
     }

     $message = esc_html(__('did import the layouts', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

function parse_layout_doc($svg_path){

// grabs plain svg file
     $ldd = @file_get_contents($svg_path);
     if(null == $ldd){ return false; }

// sets up a parser
     $parser = xml_parser_create();
     xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
     xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
     xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
     xml_parse_into_struct($parser, trim($ldd), $svg_doc);
     xml_parser_free($parser);

// grabs mock spread json file
     $path = WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'layout-draft'.DIRECTORY_SEPARATOR.'mock-spread.json';
     $spread = @file_get_contents($path);
     if(null == $spread){ return false; }

// parses json 
     $doc = json_decode($spread);
     if(null == $doc){ return false; }
     
     $doc = walk_the_doc($doc);

// desired ppi at configurable 300 ppi
     $doc['ppi'] = Layout::DESIRED_PPI;
     $doc['unit'] = 'px';
     $doc['assets'] = [];

// the exported svg documents come up with some client units of 1132 
// which is A4 kind of which is 2500px at 300ppi
// which is constructed at 72ppi i guess
     $doc['assumed_ppi_of_origin'] = Layout::ASSUMED_SVG_UNIT;

     $svg_doc = flatten_groups($svg_doc);

// size of the svg document as in the view box and some masks
     $res = eval_doc_size($svg_doc, $doc);

     $doc['printSize']['width'] = $res['doc_width'];
     $doc['printSize']['height'] = $res['doc_height'];
     $doc['doc_x_offset'] = $res['doc_x_offset'];
     $doc['doc_y_offset'] = $res['doc_y_offset'];
     $doc['origin'] = $svg_path;

// text filds within the svg 
     $res = eval_text_fields($svg_doc, $doc);
     $doc['assets'] = array_merge($doc['assets'], $res);

// polygon fields in grey is the image slots
     $res = eval_polygon_fields($svg_doc, $doc);

// layout code is to be determined such as LPP 
// landscape portrait portrait
     $doc['layout']['code'] = get_layout_code_of_spread($res);

// insert of mock image assets
     $res = insert_image_assets($doc, $res);
// scale and fit of the image assets as is defined in the config
     $res = fit_image_assets_into_slot($doc, $res);
     $doc['assets'] = array_merge($doc['assets'], $res);

// path fields as hearts and circls and such
// and then sometimes the image slots is paths too
     $res = eval_path_fields($svg_doc, $doc);
     $res = insert_image_assets($doc, $res);
     $doc['assets'] = array_merge($doc['assets'], $res);

     return $doc;
}

function corr_layout_pos($val, $doc){
     $res = null;
     $val = floatval($val);
     switch($doc['unit']){
          case 'px':
               $res = px_pump($val, $doc['assumed_ppi_of_origin'], $doc['ppi']);
               break;
          default:
               $res = px_to_unit($doc['assumed_ppi_of_origin'], $val, $doc['ppi']);
               break;
     }

     return $res;
}

function corr_path_d($d, $doc){

     $d = preg_replace('/e\-\d+/', '', $d);
     $d = sprintf('%sx', $d);

     preg_match_all('/([a-zA-Z])(.*?)(?=[a-zA-Z])/', $d, $temp);
     $buf = '';
     for($idx = 0; $idx < count($temp[1]); $idx++){
          $command = $temp[1][$idx];

// 1,2-3 which is 1,2,-3
          $chunk = str_replace(',-', '-', $temp[2][$idx]);
          $chunk = str_replace('-', ',-', $chunk);

// M 1,2 V 1,2 c 1,2...
          switch($command){

// m as in move to
               case 'm': case 'M':
               case 'l': case 'L':
               case 'c': case 'C': 
               case 's': case 'S':
                    $t = explode(' ', $chunk);
                    $k = [];
                    foreach($t as $b){
                         $n = explode(',', $b);
                         foreach($n as $f){
                              if('' != $f){
                                   $k[]= corr_layout_pos($f, $doc);
                              }
                         }
                    }
                    $buf.= sprintf('%s %s ', $command, implode(' ', $k));
                    break;

// arc
               case 'a': case 'A':
                    $ary = explode(',', $chunk);
                    $r = [];
                    $c = 0;
                    foreach($ary as $i){
                         if(null == $i){ continue; }
                         switch($c){
                             case 2:
                             case 3:
                             case 4:
                                  $r[]= $i;
                                  break;
                             default:
                                  $r[]= corr_layout_pos($i, $doc);;
                                  break;
                         }
                         $c++;
                    }
                    $rcc = implode(' ', $r);
                    $buf.= sprintf('%s %s ', $command, $rcc);
                    break;

// horizontal and vertical paths
               case 'h': case 'H':
               case 'v': case 'V':
                    $chunk = preg_replace('/,/', '', $chunk);
                    $chunk = preg_replace('/\s+/', '', $chunk);
                    $pos = corr_layout_pos($chunk, $doc);
                    $buf.= sprintf('%s %s ', $command, $pos);
                    break;

// close of a path
               case 'z': case 'Z':
                    $buf.= sprintf('%s', $command);
                    break;
          }
     }

     return $buf;
}

function eval_path_fields($svg_doc, $doc){

     $idx = 0;
     $res = [];
     $d = 0;

     foreach($svg_doc as $node){

          switch($node['tag']){

               case 'path':

                    $asset = [];
                    $asset['type'] = 'path';
                    $asset['conf'] = [];
                    $asset['conf']['unit'] = $doc['unit'];
                    $asset['conf']['depth'] = $d;

                    $asset['indx'] = sprintf('path_%s', $idx);
                    $asset['path'] = corr_path_d($node['attributes']['d'], $doc);

// css style attribute
                    $css = $node['attributes']['style'];
                    if(!is_null($css)){
                         $style = get_style_coll_from_attribute($css);
                         $color = $style['fill'];
                         $asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($color));

// assume asset is a grey
                         if(is_grey_hex($color)){;

                              $asset['slot'] = true;


// relative and absolut v and h values
                              $asset['path'] = preg_replace('/^m/', 'M', $asset['path']);
                              preg_match(
                                   '/^(M)\s(.*?)\s(.*?)\s([a-z])\s(.*?)\s([a-z])\s(.*?)\s([a-z])\s(.*?)\s([a-z])\s(.*?)\s/i',
                                        $asset['path'],
                                        $mtch
                              );

                              if(!empty($mtch)){

                                   if('v' == $mtch[4]){
                                        $mtch[4] = 'V';
                                        $mtch[5] = floatval($mtch[3]) +floatval($mtch[5]);
                                   }
                                   if('v' == $mtch[6]){
                                        $mtch[6] = 'V';
                                        $mtch[7] = floatval($mtch[3]) +floatval($mtch[7]);
                                   }
                                   if('v' == $mtch[8]){
                                        $mtch[8] = 'V';
                                        $mtch[9] = floatval($mtch[5]) +floatval($mtch[9]);
                                   }
                                   if('v' == $mtch[10]){
                                        $mtch[10] = 'V';
                                        $mtch[11] = floatval($mtch[7]) +floatval($mtch[11]);
                                   }

                                   if('h' == $mtch[4]){
                                        $mtch[4] = 'H';
                                        $mtch[5] = floatval($mtch[2]) +floatval($mtch[5]);
                                   }
                                   if('h' == $mtch[6]){
                                        $mtch[6] = 'H';
                                        $mtch[7] = floatval($mtch[2]) +floatval($mtch[7]);
                                   }
                                   if('h' == $mtch[8]){
                                        $mtch[8] = 'H';
                                        $mtch[9] = floatval($mtch[5]) +floatval($mtch[9]);
                                   }
                                   if('h' == $mtch[10]){
                                        $mtch[10] = 'H';
                                        $mtch[11] = floatval($mtch[7]) +floatval($mtch[11]);
                                   }

                                   array_shift($mtch);
                                   $asset['path'] = implode(' ', $mtch);
                              }

                              preg_match(
                                   '/^(M)\s(.*?)\s(.*?)\s([a-z])\s(.*?)\s([a-z])\s(.*?)\s([a-z])\s(.*?)\s([a-z])\s(.*?)$/i',
                                        $asset['path'], 
                                        $mtch
                              );

                              if(!empty($mtch)){

                                   if('H' == $mtch[4]){

                                        $xs = [ floatval($mtch[2]), floatval($mtch[5]), floatval($mtch[9]) ];
                                         sort($xs); $xmin = floatval($xs[0]);
                                        rsort($xs); $xmax = floatval($xs[0]);

                                        $ys = [ floatval($mtch[3]), floatval($mtch[7]), floatval($mtch[11]) ];
                                         sort($ys); $ymin = floatval($ys[0]);
                                        rsort($ys); $ymax = floatval($ys[0]);
                                   
                                        $asset['conf']['xpos'] = $xmin;
                                        $asset['conf']['ypos'] = $ymin;
                                        $asset['conf']['width'] = $xmax -$xmin;
                                        $asset['conf']['height'] = $ymax -$ymin;

                                        $asset['layout_code'] = 'P';
                                        if(floatval($asset['conf']['width']) >= floatval($asset['conf']['height'])){ 
                                             $asset['layout_code'] = 'L';
                                        }

                                   }

                                   if('V' == $mtch[4]){
                                        $xs = [ floatval($mtch[2]), floatval($mtch[7]), floatval($mtch[11]) ];
                                         sort($xs); $xmin = $xs[0];
                                        rsort($xs); $xmax = $xs[0];

                                        $ys = [ floatval($mtch[3]), floatval($mtch[5]), floatval($mtch[9]) ];
                                         sort($ys); $ymin = $ys[0];
                                        rsort($ys); $ymax = $ys[0];

                                        $asset['conf']['xpos'] = $xmin;
                                        $asset['conf']['ypos'] = $ymin;
                                        $asset['conf']['width'] = $xmax -$xmin;
                                        $asset['conf']['height'] = $ymax -$ymin;

                                        $asset['layout_code'] = 'P';
                                        if(floatval($asset['conf']['width']) >= floatval($asset['conf']['height'])){ 
                                             $asset['layout_code'] = 'L';
                                        }
                                   }
                              }
                         }
                    }
// push
                    $res[]= $asset;
                    $idx++;
                    break;
          }

          $d += Layout::Y_STEP;
      }

      return $res;
}

function eval_polygon_fields($svg_doc, $doc){

     $res = [];
     $indx = intval(0);
     $d = 0;

     foreach($svg_doc as $node){

          switch($node['tag']){

               case 'polygon':

// asset
                    $asset = [];
                    $asset['type'] = 'poly';
                    $asset['indx'] = sprintf('poly_%s', intval($indx));
// asset conf
                    $asset['conf'] = [];
                    $asset['conf']['unit'] = 'px';
                    $asset['conf']['depth'] = $d;
                    $asset['conf']['color'] = [];

// points of the poly
                    $points = $node['attributes']['points'];
                    $points = trim(str_replace(',', ' ', $node['attributes']['points']));
                    $points = explode(' ', $points);
                    for($idx = 0; $idx < count($points); $idx++){
                         $points[$idx] = corr_layout_pos($points[$idx], $doc);
                    } 

// xpositions of a rect
                    $xt = [];
                    for($idx = 0; $idx < count($points); $idx+= 2){ 
                         $xt[]= $points[$idx]; 
                    }

// min und max x positions 
                     sort($xt); $xmin = floatval($xt[0]);
                    rsort($xt); $xmax = floatval($xt[0]);

// ypositions of a rect
                    $yt = [];
                    for($idx = 1; $idx < count($points); $idx+= 2){ 
                         $yt[]= $points[$idx]; 
                    }

// min und max of y positions 
                     sort($yt); $ymin = floatval($yt[0]);
                    rsort($yt); $ymax = floatval($yt[0]);

// client units to defined units or px at current settings
                    $s = 2;
                    $xtmp = [];
                    foreach($points as $point){
                         $q = floatval($point);
                         $offset = floatval($doc['doc_x_offset']);
                         if(($s %2) != 0){ 
                              $offset = floatval($doc['doc_y_offset']);
                         }
                         $q+= $offset;
                         $xtmp[]= $q;
                         $s++;
                    }
                    $points = implode(' ', $xtmp);
                    $asset['points'] = $points;

                    $asset['slot'] = false;

// evaluates the style af a polygon
                    $css = $node['attributes']['style'];
                    if(null != $css){
                         $style = get_style_coll_from_attribute($css);
                         $color = $style['fill'];
                         $asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($color));

// whether asset is slot or not
                         if(is_grey_hex($color)){;

                              $asset['slot'] = true;
                              $asset['conf']['xpos'] = floatval($xmin) +$doc['doc_x_offset'];
                              $asset['conf']['ypos'] = floatval($ymin) +$doc['doc_y_offset'];
                              $asset['conf']['width'] = $xmax -$xmin;
                              $asset['conf']['height'] = $ymax -$ymin;

                              $asset['layout_code'] = 'P';
                              if(floatval($asset['width']) >= floatval($asset['height'])){ 
                                   $asset['layout_code'] = 'L';
                              }
                         }
                    }

                    $res[]= $asset;
                    $indx++;
                    break;
          }

          $d += Layout::Y_STEP;
     }

     return $res;
}

function eval_text_fields($svg_doc, $doc){

     $text_fields = [];
     $buf = '';
     $d = 0;

// adds up text fields until the note of width and height is found

     foreach($svg_doc as $node){

          switch($node['tag']){

               case 'text':
// text node has style information
                    $class = $node['attributes']['class'];
                    $style = $node['attributes']['style'];
                    $cls = null == $class ? $style : null;
                    $cls = null == $style ? null : $style;
                    break;

               case 'tspan':
// buf adds up until such: 'x 42.52 px y 96.378 px w 363.78 px middle h 253.622 px'
                    $buf.= sprintf('%s ', $node['value']);
                    preg_match('/x(.{0,64}?)px.{0,64}?y(.{0,64}?)px.{0,64}?w(.{0,64}?)px.{0,64}?h(.{0,64}?)px/i', $buf, $mtch);
                    if(!is_null($mtch[4])){
                         $temp = [];
                         $temp['pos_descriptor'] = $buf;
                         $temp['depth'] = $d;
                         $temp['style'] = get_style_coll_from_attribute($cls);
                         $text_fields[]= $temp;
                         $buf = '';
                         $cls = null;
                    }
                    break;
          }

// depth as in z-sort
          $d += Layout::Y_STEP;
     }

// random words
     $random_span_ary = file(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'mock.txt');
     $tmp = [];
     foreach($random_span_ary as $row){
          $tmp[]= trim_for_print($row);
     }
     $random_span_ary = $tmp;

     $indx = 0;
     $res = [];
     $line = 1.35;
     foreach($text_fields as $field){

          preg_match('/x(.{0,64}?)px.{0,64}?y(.{0,64}?)px.{0,64}?w(.{0,64}?)px.{0,64}?h(.{0,64}?)px/i', $field['pos_descriptor'], $mtch);

          $xpos = $mtch[1];
          $xpos = preg_replace('/\s+/i', '', $xpos);
          $xpos = floatval($xpos);
          $xpos = corr_layout_pos($xpos, $doc);

          $ypos = $mtch[2];
          $ypos = preg_replace('/\s+/i', '', $ypos);
          $ypos = floatval($ypos);
          $ypos = corr_layout_pos($ypos, $doc);

          $width = $mtch[3];
          $width = preg_replace('/\s+/i', '', $width);
          $width = floatval($width);
          $width = corr_layout_pos($width, $doc);

          $height = $mtch[4];
          $height = preg_replace('/\s+/i', '', $height);
          $height = floatval($height);
          $height = corr_layout_pos($height, $doc);

          $font_size = trim($field['style']['font-size']);
          $font_size = preg_replace('/[^\d]/i', '', $font_size);
          $font_size = floatval($font_size);
          $font_size = corr_layout_pos($font_size, $doc);
          if(0 >= $font_size){ $font_size = 1; }

          $font_family = match_font_family($field['style']);
          $font_weight = match_font_weight($field['style']);
          $color = rgb2cmyk(hex2rgb($field['style']['fill']));

          $txts = [];
          $max_spans = intval(floatval($height) /(floatval($font_size) *floatval($line)));
          for($idx = 0; $idx <= $max_spans; $idx++){
               $row = random_int(0, count($random_span_ary) -1);
               $txts[$idx] = $random_span_ary[$row];
          }

          $depth = intval($field['depth']);

// asset of type text
          $asset = [];
          $asset['type'] = 'text';
          $asset['indx'] = sprintf('text_%s', $indx);
          $asset['text'] = $txts;

          $asset['conf'] = [];

          $asset['conf']['font'] = [];
          $asset['conf']['font']['family'] = $font_family;
          $asset['conf']['font']['size'] = $font_size;
          $asset['conf']['font']['lineHeight'] = floatval($font_size) *floatval($line);
          $asset['conf']['font']['align'] = 'left';
          $asset['conf']['font']['space'] = '1';
          $asset['conf']['font']['weight'] = $font_weight;

          $asset['conf']['unit'] = 'px';
          $asset['conf']['xpos'] = $xpos;
          $asset['conf']['ypos'] = $ypos;
          $asset['conf']['width'] = $width;
          $asset['conf']['height'] = $height;
          $asset['conf']['opacity'] = '1';
          $asset['conf']['depth'] = $depth;

          $asset['conf']['color'] = [];
          $asset['conf']['color']['cmyk'] = $color;

          $indx++;
          $res[]= $asset;
     }

     return $res;
}

function eval_doc_size($svg_doc, $doc){

     $res['doc_width'] = 0;
     $res['doc_height'] = 0;
     $res['doc_x_offset'] = 0;
     $res['doc_y_offset'] = 0;

     foreach($svg_doc as $node){

          switch($node['tag']){

               case 'svg':
// doc size is typed in the view box attribute
                    $view_box = $node['attributes']['viewBox'];
                    if(false == is_null($view_box)){
                         $temp = explode(' ', $view_box);
                         $res['doc_width'] = floatval($temp[2]) /2;
                         $res['doc_height'] = floatval($temp[3]);
                         $res['doc_width'] = corr_layout_pos($res['doc_width'], $doc);
                         $res['doc_height'] = corr_layout_pos($res['doc_height'], $doc);
                    }
                    break;

               case 'clipPath':
// there is a mask sometimes that manips the viewbox
                    $transform = $node['attributes']['transform'];
                    if(false == is_null($transform)){
                         preg_match('/translate\((.{0,10})\s(.{0,10})\)/', $transform, $mtc);
                         $res['doc_x_offset'] = corr_layout_pos(floatval($mtc[1]), $doc) *-1;
                         $res['doc_y_offset'] = corr_layout_pos(floatval($mtc[2]), $doc) *-1;
                    }
                    break;

               case 'rect':
// the rect within the clipmask defines the document size 
                    if(false == is_null(floatval($node['attributes']['width']))){
                         $res['doc_width'] = floatval($node['attributes']['width']) /2;
                         $res['doc_height'] = floatval($node['attributes']['height']);
                         $res['doc_width'] = corr_layout_pos($res['doc_width'], $doc);
                         $res['doc_height'] = corr_layout_pos($res['doc_height'], $doc);
                    }
                    break;
          }
     }

     return $res;
}

function fit_image_assets_into_slot($doc, $assets){
     $res = [];
     foreach($assets as $asset){
          if('image' == $asset['type']){
               $res[]= fit_image_asset_into_slot($doc, $asset);
          }
          else {
               $res[]= $asset;
          }
     }
     return $res;
}

function get_layout_code_of_spread($nodes){
     $res = '';
     foreach($nodes as $node){
          if(false != $node['slot']){
               $res = sprintf('%s%s', $res, $node['layout_code']);
          }
     }
     // if('' == $res){ $res = 'U'; }
     if('' == $res){ $res = 'PP'; }
     return $res;
}

function insert_image_assets($doc, $nodes){

     $res = [];

     $landscape = @file_get_contents(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.900.base');
     if(null == $landscape){ $landscape = 'missing landscape image locator'; }

     $portrait  = @file_get_contents(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.p.base');
     if(null == $portrait){ $portrait = 'missing portrait image locator'; }

     $idx = 0;

     foreach($nodes as $node){

          $res[]= $node;

// adds images
          if(true != $node['slot']){
               continue;
          }

          $chunk = 'L' == $node['layout_code'] ? $landscape : $portrait;

// sets up an image asset
          $asset = [];
          $asset['type'] = 'image'; 
          $asset['indx'] = sprintf('image_%s', $idx);
          $asset['src'] = $chunk;

// conf 
          $asset['conf'] = [];
          $asset['conf']['unit'] = 'px';
          $asset['conf']['xpos'] = $node['conf']['xpos'];
          $asset['conf']['ypos'] = $node['conf']['ypos'];
          $asset['conf']['width'] = $node['conf']['width'];
          $asset['conf']['height'] = $node['conf']['height'];
          $asset['conf']['slotW'] = $node['conf']['width'];
          $asset['conf']['slotH'] = $node['conf']['height'];
          $asset['conf']['slotX'] = $node['conf']['xpos'];
          $asset['conf']['slotY'] = $node['conf']['ypos'];
          $asset['conf']['opacity'] = '1';
          $asset['conf']['depth'] = intval($node['conf']['depth']) +1;
          $asset['conf']['maxScaleRatio'] = '1';

// fixdiss
          $r = intval($doc['ppi']) /300;
          switch(intval($doc['ppi'])){
               case 300:
                    $asset['conf']['maxScaleRatio'] = Layout::IMAGE_MAX_SCALE *$r *1;
                    break;
               case 600:
                    $asset['conf']['maxScaleRatio'] = Layout::IMAGE_MAX_SCALE *$r *2;
                    break;
          }


// image asset scales into the slot until the max scale ratio 
// and cuts the image asset by definition
          $asset['conf']['scaleType'] = Layout::IMAGE_SCALE_TYPE;
          // $asset['conf']['scaleType'] = 'no_scale';

          $res[]= $asset;
          $idx++;
     }

     return $res;
}

function eval_stylesheets($svg_doc){
     $res = [];
     foreach($svg_doc as $node){ switch($node['tag']){
          case 'style':
               preg_match_all('/\.(.{1,10})\{(.{1,1024}?)\}/', $node['value'], $temp); ;
               $res = $temp;
               break;
          }
     }
     return $res;
}

function flatten_groups($svg_doc){
     $res = $svg_doc;
     $grouped_nodes = [];
     foreach($svg_doc as $node){
          switch($node['tag']){
               case 'g':
               if(null == $node['type']){
                    $grouped_nodes[] = $node;
               }
                    break;
          }
     }
     $res = array_merge($res, $grouped_nodes);
     return $res;
}

function get_style_coll_from_attribute($style){
     $res = [];
     $tmp = explode(';', $style);
     foreach($tmp as $directive){
          $t = explode(':', $directive);
          $res[$t[0]] = $t[1];
     }
     return $res;
}

function get_style_by_selector($coll, $css){
     $res = [];
     $css = explode(' ', $css);
     foreach($css as $style){
          $idx = array_search($style, $coll[1]);
          if(false === $idx){ continue; }
          $statements = $coll[2][$idx];
          $statements = explode(';', $statements);
          foreach($statements as $statement){
               $ary = explode(':', $statement);
               if(null == $ary[0]){ continue;}
               $res[$ary[0]] = $ary[1];
          }
     }
     return $res;
}


