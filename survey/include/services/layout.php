<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_init_layout', 'exec_init_layout');
function exec_init_layout(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// fixdiss tag issus
/*
     $tags_input = $_POST['tags'];
     $term_id = wp_insert_term('term', 'post_tag', [
          'description'=>'description',
          'slug'=>'slug',
          'parent'=>0
     ]);
*/

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

add_action('admin_post_exec_get_layout_presets_by_group_and_rule', 'exec_get_layout_presets_by_group_and_rule');
function exec_get_layout_presets_by_group_and_rule(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $group = trim_incoming_filename($_POST['group']);
     $rule = trim_incoming_filename($_POST['rule']);
     $coll = get_layout_presets_by_group_and_rule($group, $rule);
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

     $ldd = @file_get_contents($svg_path);
     if(null == $ldd){ return false; }

     $parser = xml_parser_create();
     xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
     xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
     xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
     xml_parse_into_struct($parser, trim($ldd), $svg_doc);
     xml_parser_free($parser);

     $path = WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'layout-draft'.DIRECTORY_SEPARATOR.'mock-spread.json';
     $spread = @file_get_contents($path);
     if(null == $spread){ return false; }

     $doc = json_decode($spread);
     if(null == $doc){ return false; }
     
     $doc = walk_the_doc($doc);

// result at 300ppi
     $doc['unit'] = 'px';
     $doc['ppi'] = Layout::DESIRED_PPI;
     $doc['assets'] = [];

// the exported svg documents come up with some client units of 1132 
// which is A4 kind of which is 2500px at 300ppi
     $doc['assumed_ppi_of_origin'] = Layout::ASSUMED_SVG_UNIT;

     $svg_doc = flatten_groups($svg_doc);
     $css_coll = extract_stylesheets($svg_doc);;

     $res = eval_doc_size($svg_doc, $doc);

     $doc['printSize']['width'] = $res['doc_width'];
     $doc['printSize']['height'] = $res['doc_height'];
     $doc['doc_x_offset'] = $res['doc_x_offset'];
     $doc['doc_y_offset'] = $res['doc_y_offset'];
     $doc['origin'] = $svg_path;

// todo
     $res = eval_text_fields($svg_doc, $css_coll, $doc);
     $doc['assets'] = array_merge($doc['assets'], $res);

     $res = eval_polygon_fields($svg_doc, $css_coll, $doc);
     $doc['layout']['code'] = get_layout_code_of_spread($res);

     $res = insert_image_assets($doc, $res);
     $res = fit_image_assets_into_slot($doc, $res);
     $doc['assets'] = array_merge($doc['assets'], $res);

     $res = eval_path_fields($svg_doc, $css_coll, $doc);
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
     $d = sprintf('%sx', $d);
     preg_match_all('/([a-zA-Z])(.*?)(?=[a-zA-Z])/', $d, $temp);
     $buf = '';
     for($idx = 0; $idx < count($temp[1]); $idx++){
          $command = $temp[1][$idx];
          $chunk = str_replace(',-', '-', $temp[2][$idx]);
          $chunk = str_replace('-', ',-', $chunk);
          $ary = explode(',', $chunk);
          switch($command){
               case 'm': case 'M':
                    $x = corr_layout_pos($ary[0], $doc);
                    $y = corr_layout_pos($ary[1], $doc);
                    $buf.= sprintf('%s%s,%s', $command, $x, $y);
                    break;
               case 'c': case 'C': case 's': case 'S':
                    $r = [];
                    foreach($ary as $i){
                         if(null == $i){ continue; }
                         $r[]= corr_layout_pos($i, $doc);;
                    }
                    $rcc = implode(',', $r);
                    // $rcc = str_replace(',-', '-', $rcc);
                    $buf.= sprintf('%s%s', $command, $rcc);
                    break;
               case 'a': case 'A':
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
                    $rcc = implode(',', $r);
                    // $rcc = str_replace(',-', '-', $rcc);
                    $buf.= sprintf('%s%s', $command, $rcc);
                    break;
               case 'l': case 'L':
                    $x = corr_layout_pos($ary[0], $doc);
                    $y = corr_layout_pos($ary[1], $doc);
                    $buf.= sprintf('%s%s,%s', $command, $x, $y);
                    break;
               case 'z': case 'Z':
                    $buf.= sprintf('%s', $command);
                    break;
          }
     }
     return $buf;
}

function eval_path_fields($svg_doc, $css_coll, $doc){
     $res = [];
     $idx = 0;
     foreach($svg_doc as $node){
          $asset = [];
          $asset['type'] = 'path';
          $asset['conf'] = [];
          $asset['conf']['unit'] = $doc['unit'];
          $asset['conf']['depth'] = '3000';
          switch($node['tag']){
               case 'path':
                    $css = $node['attributes']['class'];
                    if(null != $css){
                         $style = get_style_by_selector($css_coll, $css);
                         $color = $style['fill'];
                         $asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($color));
                    }
                    $d = corr_path_d($node['attributes']['d'], $doc);
                    $asset['indx'] = sprintf('path_%s', $idx);
                    $asset['d'] = $d;
                    $res[]= $asset;
                    $idx++;
                    break;
          }
      }
      return $res;
}

function eval_polygon_fields($svg_doc, $css_coll, $doc){
     $res = [];
     $idx = 0;
     foreach($svg_doc as $node){
          switch($node['tag']){
               case 'polygon':

                    $poly = [];
                    $poly['type'] = 'poly';
                    $poly['indx'] = sprintf('poly_%s', $idx);
                    $poly['conf'] = [];
                    $poly['conf']['unit'] = 'px';
                    $poly['conf']['color'] = [];
                    $poly['conf']['depth'] = '100';

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
                    $poly['conf']['points'] = $points;

                    $poly['slot'] = false;

// evaluates the class af a polygon
                    $css = $node['attributes']['class'];
                    if(null != $css){
                         $style = get_style_by_selector($css_coll, $css);
                         $color = $style['fill'];

                         $poly['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($color));

// ededed and dadada is the image cut in
                         $slot_colors = ['#ededed', '#dadada', '#EDEDED', '#DADADA'];
                         $is_image_slot = false;
                         foreach($slot_colors as $slot_color){
                              if(0 == strcmp($color, $slot_color)){
                                   $is_image_slot = true;
                              }
                         }

// description of image slots
                         if($is_image_slot){
                              $poly['slot'] = true;
                              $poly['xpos'] = floatval($xmin) +$doc['doc_x_offset'];
                              $poly['ypos'] = floatval($ymin) +$doc['doc_y_offset'];
                              $poly['width'] = $xmax -$xmin;
                              $poly['height'] = $ymax -$ymin;
                              $poly['conf']['depth'] = 1000;
                              $poly['layout_code'] = 'P';
                              if(floatval($poly['width']) >= floatval($poly['height'])){ 
                                   $poly['layout_code'] = 'L';
                              }
                         }
                    }
                    $res[]= $poly;
                    $idx++;
                    break;
          }
     }
     return $res;
}

function eval_text_fields($svg_doc, $css_coll, $doc){

     $xes = [];
     $buf = '';
     foreach($svg_doc as $node){
          switch($node['tag']){
               case 'text':
                    $buf.= sprintf('%s ', $node['value']);
                    preg_match('/x(.{1,64}?)px(.{1,64}?)y(.{1,64}?)w(.{1,64}?)px(.{1,64}?)h(.{1,64}?)px/i', $buf, $mtch);
                    if(!is_null($mtch[0])){
                         $temp = [];
                         $temp['pos'] = $buf;
                         $temp['style'] = 'no style';
                         $cls = $node['attributes']['class'];
                         if(!is_null($cls)){
                              $style = get_style_by_selector($css_coll, $cls);
                              $temp['style'] = $style;
                         }
                         $buf = '';
                         $xes[]= $temp;
                    }
                    break;
          }
     }

     $tary = file(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'mock.txt');

     $indx = 0;
     $res = [];
     foreach($xes as $field){

          $txts = [];
          for($idx = 0; $idx < 13; $idx++){
               $row = random_int(0, count($tary) -1);
               $txts[$idx] = $tary[$row];
          }

          $temp = $field['pos'];
          preg_match('/x(.{1,64}?)px(.{1,64}?)y(.{1,64}?)w(.{1,64}?)px(.{1,64}?)h(.{1,64}?)px/i', $temp, $mtch);

          $xpos = $mtch[1];
          $xpos = preg_replace('/\s+/i', '', $xpos);
          $xpos = floatval($xpos);
          $xpos = corr_layout_pos($xpos, $doc);

          $ypos = $mtch[3];
          $ypos = preg_replace('/\s+/i', '', $ypos);
          $ypos = floatval($ypos);
          $ypos = corr_layout_pos($ypos, $doc);

          $width = $mtch[4];
          $width = preg_replace('/\s+/i', '', $width);
          $width = floatval($width);
          $width = corr_layout_pos($width, $doc);

          $height = $mtch[6];
          $height = preg_replace('/\s+/i', '', $height);
          $height = floatval($height);
          $height = corr_layout_pos($height, $doc);

          $font_size = trim($field['style']['font-size']);
          $font_size = preg_replace('/[^\d]/i', '', $font_size);
          $font_size = floatval($font_size);
          $font_size = corr_layout_pos($font_size, $doc);

          $font_family = preg_replace('/\s+/i', '', $field['style']['font-family']);

          $color = rgb2cmyk(hex2rgb($field['style']['fill']));

          $asset = [];
          $asset['type'] = 'text';
          $asset['indx'] = sprintf('text_%s', $indx);
          $asset['text'] = $txts;

          $asset['conf'] = [];

          $asset['conf']['font'] = [];
          $asset['conf']['font']['family'] = $font_family;
          $asset['conf']['font']['size'] = $font_size;
          $asset['conf']['font']['lineHeight'] = floatval($font_size) *1.35;
          $asset['conf']['font']['align'] = 'block';
          $asset['conf']['font']['space'] = '0.975';

          $asset['conf']['unit'] = 'px';
          $asset['conf']['xpos'] = $xpos;
          $asset['conf']['ypos'] = $ypos;
          $asset['conf']['width'] = $width;
          $asset['conf']['height'] = $height;
          $asset['conf']['opacity'] = '1';
          $asset['conf']['depth'] = '25000';

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

// doc size is typed in the view box attribute
               case 'svg':
                    $view_box = $node['attributes']['viewBox'];
                    if(false == is_null($view_box)){
                         $temp = explode(' ', $view_box);
                         $res['doc_width'] = floatval($temp[2]) /2;
                         $res['doc_height'] = floatval($temp[3]);
                         $res['doc_width'] = corr_layout_pos($res['doc_width'], $doc);
                         $res['doc_height'] = corr_layout_pos($res['doc_height'], $doc);
                    }
                    break;

// there is a mask sometimes that manips the viewbox
               case 'clipPath':
                    $transform = $node['attributes']['transform'];
                    if(false == is_null($transform)){
                         preg_match('/translate\((.{0,10})\s(.{0,10})\)/', $transform, $mtc);
                         $res['doc_x_offset'] = corr_layout_pos(floatval($mtc[1]), $doc) *-1;
                         $res['doc_y_offset'] = corr_layout_pos(floatval($mtc[2]), $doc) *-1;
                    }
                    break;

// the rect within the clipmask defines the document size 
               case 'rect':
                    if(false == is_null(floatval($node['attributes']['width']) /2)){
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

/*

*/

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
     if('' == $res){ $res = 'U'; }
     return $res;
}

function insert_image_assets($doc, $polys){

     $res = [];
     $idx = 0;

     $landscape = @file_get_contents(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.900.base');
     $portrait = @file_get_contents(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.p.base');

     if(null == $portrait){ $portrait = 'missing portrait image locator'; }
     if(null == $landscape){ $landscape = 'missing landscape image locator'; }

     foreach($polys as $node){
          if(false != $node['slot']){
               $chunki = 'L' == $node['layout_code'] ? $landscape : $portrait;
               $asset = [];
               $asset['type'] = 'image'; 
               $asset['indx'] = sprintf('image_%s', $idx);
               $asset['src'] = $chunki;
               $asset['conf'] = [];
               $asset['conf']['unit'] = 'px';
               $asset['conf']['xpos'] = $node['xpos'];
               $asset['conf']['ypos'] = $node['ypos'];
               $asset['conf']['width'] = $node['width'];
               $asset['conf']['height'] = $node['height'];
               $asset['conf']['slotW'] = $node['width'];
               $asset['conf']['slotH'] = $node['height'];
               $asset['conf']['slotX'] = $node['xpos'];
               $asset['conf']['slotY'] = $node['ypos'];
               $asset['conf']['opacity'] = '1';
               $asset['conf']['depth'] = intval(10000) +intval($idx);

// diss i am not sure about.. assumed 200dpi is enough at 300dpi dunno
               $asset['conf']['maxScaleRatio'] = '1';
               $r = intval($doc['ppi']) /300;
               switch(intval($doc['ppi'])){
                    case 300: 
                         $asset['conf']['maxScaleRatio'] = Layout::IMAGE_MAX_SCALE_RATIO *$r;
                         break;
                    case 600:
                         $asset['conf']['maxScaleRatio'] = Layout::IMAGE_MAX_SCALE_RATIO *$r;
                         break;
               }

// image asset scales into the slot until the max scale ratio 
// and cuts the image asset by definition
               $asset['conf']['scaleType'] = Layout::IMAGE_SCALE_TYPE;


// image assets scales into the widht or into the height of the slot
// without cutting the image asset
// until the max scale ratio 
               // $asset['conf']['scaleType'] = 'no_scale';
               $res[]= $asset;
               $idx++;
          }
          $res[]= $node;
     }
     return $res;
}

function extract_stylesheets($svg_doc){
     $res = [];
     foreach($svg_doc as $node){ switch($node['tag']){
          case 'style':
               preg_match_all('/\.(.{1,10})\{(.{1,1024}?)\}/', $node['value'], $temp); ;
               $res = $temp;
               break;
          }
     }
     // @file_put_contents('/tmp/out', json_encode($res, JSON_PRETTY_PRINT), FILE_APPEND);
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


