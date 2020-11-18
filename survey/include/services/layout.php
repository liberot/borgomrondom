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
          $res = init_layout_doc($svg_path);
          $doc = $res['doc'];
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

function init_layout_doc($svg_path){

// the exported svg documents come up with some client units of 1132 
// which is A4 kind of which is 2500px at 300ppi
     $assumed_ppi = 72;
     // $assumed_ppi = 113;

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

// assume result at 300ppi
     $doc['unit'] = 'px';
     $doc['ppi'] = 300;
     $doc['assets'] = [];

     $poly_nodes = [];
     $path_nodes = [];

     $xoffset = null;
     $yoffset = null;

     $svg_doc = flatten_groups($svg_doc);
     $css_coll = extract_stylesheets($svg_doc);;

     $doc_width = null;
     $doc_height = null;
     $string_nodes = [];

     $yline = null;
     $string_nodes = [];
     $line_buf = '';

     $prev_node = '';
     $doc_clip_with_corr_parse = null;

     foreach($svg_doc as $node){

          switch($node['tag']){

// viewbox partly is the size of the spread
               case 'svg':
                    $view_box = $node['attributes']['viewBox'];
                    if(false == is_null($view_box)){
                         $temp = explode(' ', $view_box);
                         $doc_width = floatval($temp[2]) /2;
                         $doc_height = floatval($temp[3]);
                    }
                    break;

// clippath describes the width and the height also
               case 'clipPath':
                    $transform = $node['attributes']['transform'];
                    preg_match('/translate\((.{0,10})\s(.{0,10})\)/', $transform, $mtc);
                    if(null == $xoffset){
                         $xoffset = floatval($mtc[1]);
                         $yoffset = floatval($mtc[2]);
                         if('px' == $doc['unit']){
                              $xoffset = px_pump($xoffset, $assumed_ppi, $doc['ppi']) *-1;
                              $yoffset = px_pump($yoffset, $assumed_ppi, $doc['ppi']) *-1;
                         }
                         else {
                              $xoffset = px_to_unit($assumed_ppi, $xoffset, $doc['unit']) *-1;
                              $yoffset = px_to_unit($assumed_ppi, $yoffset, $doc['unit']) *-1;
                         }
                    }
                    break;

               case 'rect':
                    if(is_null($doc_clip_with_corr_parse)){
                         $doc_clip_with_corr_parse = true;
                         $doc_width = floatval($node['attributes']['width']) /2;
                         $doc_height = floatval($node['attributes']['height']);
                    }

                    break;

// text is to be done: there is notices in the text fields as from now on
               case 'text':
                    $pos = $node['attributes']['transform'];
                    preg_match('/translate\((.{0,10})\s(.{0,10})\)/', $pos, $mtc);
                    $xpos = floatval($mtc[1]);
                    $ypos = floatval($mtc[2]);
                    $text = $node['value'];
                    $font_size = 18;
                    $font_family = 'American Typewriter';
                    $font_fill = '#000000';
                    $font_weight = 300;
                    $css = $node['attributes']['class'];
                    if(null != $css){
                         $style = get_style_by_selector($css_coll, $css);
                         $font_size = str_replace('px', '', $style['font-size']);
                         $font_family = $style['font-family'];
                         $font_fill = $style['fill'];
                         $font_weight = $style['font-weight'];
                    }
                    if(null == $yline){ 
                         $yline = $ypos; 
                         $line_xpos = $xpos;
                         $line_ypos = $ypos;
                         $line_buf = '';
                    }
                    if($yline < $ypos){
                         $n = [];
                         $n['text'] = $line_buf;
                         $n['xpos'] = px_pump($xpos, $assumed_ppi, $doc['ppi']) +$xoffset;
                         $n['ypos'] = px_pump($ypos, $assumed_ppi, $doc['ppi']) +$yoffset;
                         $n['font_size'] = px_pump($font_size, $assumed_ppi, $doc['ppi']);
                         $n['font_family'] = $font_family;
                         $n['font_fill'] = $font_fill;
                         $n['font_weight'] = $font_weight;
                         $string_nodes[]= $n;

                         $yline = $ypos;
                         $line_buf = '';
                         $line_xpos = $xpos;
                         $line_ypos = $ypos;
                    }
                    $line_buf = sprintf('%s%s%s', $line_buf, $text, ' ');
                    break;

// polys of color '#ededed' are defined image asset slots
// polys of color '#dadada' are defined image asset slots
               case 'polygon':
                    $ptmp = $node['attributes']['points'];
                    $ptmp = trim(str_replace(',', ' ', $node['attributes']['points']));
                    $ptmp = explode(' ', $ptmp);

// xpositions of a rect
                    $xt = [];
                    for($idx = 0; $idx < count($ptmp); $idx+= 2){ 
                         $xt[]= $ptmp[$idx]; 
                    }

// min und max x positions 
                     sort($xt); $xmin = floatval($xt[0]);
                    rsort($xt); $xmax = floatval($xt[0]);

// ypositions of a rect
                    $yt = [];
                    for($idx = 1; $idx < count($ptmp); $idx+= 2){ 
                         $yt[]= $ptmp[$idx]; 
                    }

// min und max of y positions 
                     sort($yt); $ymin = floatval($yt[0]);
                    rsort($yt); $ymax = floatval($yt[0]);

// client units to defined units or px at current settings
                    $s = 2;
                    $xtmp = [];
                    foreach($ptmp as $p){
                         $q = floatval($p);
                         if('px' == $doc['unit']){ $q = px_pump($q, $assumed_ppi, $doc['ppi']); }
                         else { $q = px_to_unit($assumed_ppi, $q, $doc['unit']); }
                         $offset = $xoffset;
                         if(($s %2) != 0){ $offset = $yoffset; }
                         $q += $offset;
                         $xtmp[]= $q;
                         $s++;
                    }

                    $ptmp = implode(' ', $xtmp);
                    $node['attributes']['points'] = $ptmp;

// evaluates the class af a polygon
                    $css = $node['attributes']['class'];
                    if(null != $css){
                         $style = get_style_by_selector($css_coll, $css);
                         $color = $style['fill'];

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
                              $node['slot'] = true;
                              $node['xpos'] = floatval($xmin);
                              $node['ypos'] = floatval($ymin);
                              $node['width'] = $xmax -$xmin;
                              $node['height'] = $ymax -$ymin;
                              $node['layout_code'] = 'P';

                              if(floatval($node['width']) >= floatval($node['height'])){ 
                                   $node['layout_code'] = 'L';
                              }
                             
// @file_put_contents('/tmp/out', json_encode($node, JSON_PRETTY_PRINT), FILE_APPEND);

                              if('px' == $doc['unit']){
                                   $node['xpos'] = px_pump($node['xpos'], $assumed_ppi, $doc['ppi']);
                                   $node['ypos'] = px_pump($node['ypos'], $assumed_ppi, $doc['ppi']);
                                   $node['width'] = px_pump($node['width'], $assumed_ppi, $doc['ppi']);
                                   $node['height'] = px_pump($node['height'], $assumed_ppi, $doc['ppi']);
                              }
                              else {
                                   $node['xpos'] = px_to_unit($assumed_ppi, $node['xpos'], $doc['unit']);
                                   $node['ypos'] = px_to_unit($assumed_ppi, $node['ypos'], $doc['unit']);
                                   $node['width'] = px_to_unit($assumed_ppi, $node['width'], $doc['unit']);
                                   $node['height'] = px_to_unit($assumed_ppi, $node['height'], $doc['unit']);
                              }
                              $node['xpos']+= $xoffset;
                              $node['ypos']+= $yoffset;
                              $node['depth']+= 750;
                         }
                    }
                    $node['attributes']['color'] = $color;
                    $poly_nodes[]= $node;
                    break;

// paths is circles and strokes mainly
               case 'path':

                    $node['type'] = 'path';
                    $node['unit'] = 'px';
                    $node['d'] = $d;

                    $css = $node['attributes']['class'];
                    if(null != $css){
                         $style = get_style_by_selector($css_coll, $css);
                         $node['style'] = $style;
                         $d = $node['attributes']['d'];
                         preg_match('/M(.{1,128}?)c/', $d, $move);
                         preg_match('/([C])(.*?)[a-zA-Z]/', $d, $xcurves);
                         preg_match('/([L])(.*?)[a-zA-Z]/', $d, $xlines);
                         preg_match('/([c])(.*?)[a-zA-Z]/', $d, $curves);
                         preg_match('/([l])(.*?)[a-zA-Z]/', $d, $lines);
                         $node['d'] = $d;

/*
@file_put_contents('/tmp/out', json_encode($move, JSON_PRETTY_PRINT), FILE_APPEND);
@file_put_contents('/tmp/out', json_encode($curves, JSON_PRETTY_PRINT), FILE_APPEND);
@file_put_contents('/tmp/out', json_encode($xcurves, JSON_PRETTY_PRINT), FILE_APPEND);
@file_put_contents('/tmp/out', json_encode($lines, JSON_PRETTY_PRINT), FILE_APPEND);
@file_put_contents('/tmp/out', json_encode($xlines, JSON_PRETTY_PRINT), FILE_APPEND);
@file_put_contents('/tmp/out', json_encode($d, JSON_PRETTY_PRINT), FILE_APPEND);
*/

/*
M566.93-14.17v737
M230.92,464.68A174.94,174.94,0,1,0,56,289.74,174.94,174.94,0,0,0,230.92,464.68Z
M566.93-14.17v737
M876.29,652a66.77,66.77,0,1,0-66.76-66.77A66.77,66.77,0,0,0,876.29,652Z
M876.53,651.97c36.87,0,66.76-29.89,66.76-6
print_r($d);
print "\n";
*/
                         preg_match(  '/M(.{1,64}?)[v]/', $d, $vmc);
                         preg_match('/M(.{1,64}?)(a|A)/', $d, $cmc);
                         preg_match('/(a|A)(.{1,10}?),/', $d, $rmc);
                         preg_match(  '/M(.{1,64}?)(c)/', $d, $kmc);
                         preg_match(  '/(c)(.{1,10}?),/', $d, $xmc);

// @file_put_contents('/tmp/out', json_encode($kmc, JSON_PRETTY_PRINT), FILE_APPEND);
// @file_put_contents('/tmp/out', json_encode($xmc, JSON_PRETTY_PRINT), FILE_APPEND);
// @file_put_contents('/tmp/out', json_encode(  $d, JSON_PRETTY_PRINT), FILE_APPEND);

                         if(2 <= count($cmc)){
                              $node['type'] = 'circle';
                              $temp = explode(',', $cmc[1]);

                              $node['xpos'] = floatval($temp[0]);
                              $node['ypos'] = floatval($temp[1]);
                              $node['diam'] = floatval($rmc[2]);

                              if('px' == $doc['unit']){
                                   $node['xpos'] = px_pump($node['xpos'], $assumed_ppi, $doc['ppi']);
                                   $node['ypos'] = px_pump($node['ypos'], $assumed_ppi, $doc['ppi']);
                                   $node['diam'] = px_pump($node['diam'], $assumed_ppi, $doc['ppi']);
                              }
                              else {
                                   $node['xpos'] = px_to_unit($assumed_ppi, $node['xpos'], $doc['unit']);
                                   $node['ypos'] = px_to_unit($assumed_ppi, $node['ypos'], $doc['unit']);
                                   $node['diam'] = px_to_unit($assumed_ppi, $node['diam'], $doc['unit']);
                              }

                              $node['diam']*= 2;
                              $node['ypos']-= $node['diam'] *1;
                              $node['xpos']-= $node['diam'] /2;
                         }
                    }
                    $path_nodes[]= $node;
                    break;
          }
          $prev_node = $node['tag'];
     }

// printsize is custom beats me it is not A4 or such
     $doc['printSize']['idx'] = 'xX';
     if('px' == $doc['unit']){
          $doc_width = px_pump($doc_width, $assumed_ppi, $doc['ppi']);
          $doc_height = px_pump($doc_height, $assumed_ppi, $doc['ppi']);
     }
     else{
          $doc_width = intval(px_to_unit($assumed_ppi, $w, $doc['unit']));
          $doc_height = intval(px_to_unit($assumed_ppi, $h, $doc['unit']));
     }
     $doc['printSize']['width'] = $doc_width;
     $doc['printSize']['height'] = $doc_height;

// places the assets into the documents
     $doc['assets'] = array_merge($doc['assets'], extract_text_assets($string_nodes));
     $doc['assets'] = array_merge($doc['assets'], extract_poly_assets($poly_nodes));
     $doc['assets'] = array_merge($doc['assets'], extract_path_assets($path_nodes));

// sets the *analyzed layout chiffre of the parsed spread as in L or LPP or such
// characteristica of the image slots like 3xL 
     $doc['layout']['code'] = get_layout_code_of_spread($poly_nodes);

// inserts image assets into the layout as for debug reasons
// real image assets be placed into the document once the assets is uploaded
     $res = insert_image_assets($doc, $poly_nodes);
     $res = fit_image_assets_to_slot($doc, $res);
     $doc['assets'] = array_merge($doc['assets'], $res);

     $res = ['doc'=>$doc, 'svg_doc'=>$svg_doc];

     return $res;
}


function fit_image_assets_to_slot($doc, $assets){
     $res = [];
     foreach($assets as $asset){
          $res[]= fit_image_asset_to_slot($doc, $asset);
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

function insert_image_assets($doc, $poly_nodes){

     $res = [];
     $idx = 0;

     $landscape = @file_get_contents(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.900.base');
     $portrait = @file_get_contents(WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.p.base');

     if(null == $portrait){ $portrait = 'missing portrait image locator'; }
     if(null == $landscape){ $landscape = 'missing landscape image locator'; }

     foreach($poly_nodes as $node){

          if(false != $node['slot']){

               $chunki = 'L' == $node['layout_code'] ? $landscape : $portrait;

               $image_asset = [];
               $image_asset['type'] = 'image'; 
               $image_asset['indx'] = sprintf('image_%s', $idx);
               $image_asset['src'] = $chunki;
               $image_asset['conf'] = [];
               $image_asset['conf']['unit'] = 'px';
               $image_asset['conf']['xpos'] = $node['xpos'];
               $image_asset['conf']['ypos'] = $node['ypos'];
               $image_asset['conf']['width'] = $node['width'];
               $image_asset['conf']['height'] = $node['height'];
               $image_asset['conf']['slotW'] = $node['width'];
               $image_asset['conf']['slotH'] = $node['height'];
               $image_asset['conf']['slotX'] = $node['xpos'];
               $image_asset['conf']['slotY'] = $node['ypos'];
               $image_asset['conf']['opacity'] = '1';
               $image_asset['conf']['depth'] = intval(10000) +intval($idx);

// diss i am not sure about.. assumed 200dpi is enough at 300dpi dunno
               $image_asset['conf']['maxScaleRatio'] = '1';
               switch(intval($doc['ppi'])){
                    case 300: 
                         $image_asset['conf']['maxScaleRatio'] = '1.5';
                         break;
                    case 600:
                         $image_asset['conf']['maxScaleRatio'] = '3.0';
                         break;
               }

// image asset scales into the slot until the max scale ratio 
// and cuts the image asset by definition
               $image_asset['conf']['scaleType'] = 'cut_into_slot';

// image assets scales into the widht or into the height of the slot
// without cutting the image asset
// until the max scale ratio 
               // $image_asset['conf']['scaleType'] = 'no_scale';

               $res[]= $image_asset;
               $idx++;
          }
     }
     return $res;
}

function extract_path_assets($path_nodes){

     $res = [];
     $idx = 0;
     foreach($path_nodes as $node){
          switch($node['type']){
               case 'circle':
                    $asset = [];
                    $asset['type'] = 'circle';
                    $asset['indx'] = sprintf('circle_%s', $idx);
                    $asset['conf'] = [];
                    $asset['conf']['unit'] = $node['unit'];
                    $asset['conf']['xpos'] = $node['xpos']; 
                    $asset['conf']['ypos'] = $node['ypos'];
                    $asset['conf']['diam'] = $node['diam'];
                    $asset['conf']['width'] = 0;
                    $asset['conf']['height'] = 0;
                    $asset['conf']['color'] = [];
                    $asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($node['style']['fill']));
                    $res[] = $asset;
                    break;
               case 'path':
                    $asset = [];
                    $asset['type'] = 'path';
                    $asset['indx'] = sprintf('path_%s', $idx);
                    $asset['conf'] = [];
                    $asset['conf']['unit'] = $node['unit'];
                    $asset['d'] = $node['d'];
                    $res[] = $asset;
                    break;
          }
          $idx++;
     }
     return $res;
}

// todo text asset noticees
function extract_text_assets($string_nodes){

     $res = [];
     $xes = [];
     $yes = [];
     $txt = [];

     $font_family = $string_nodes[0]['font_family'];
     $font_weight = $string_nodes[0]['font_weight'];
     $font_size = $string_nodes[0]['font_size'];
     $font_fill = $string_nodes[0]['font_fill'];
     $font_space = '0';

     foreach($string_nodes as $node){
          $xes[]= $node['xpos'];
          $yes[]= $node['ypos'];
          $txt[]= $node['text'];
     }

     sort($xes);
     sort($yes);
     $xpos = $xes[0];
     $ypos = $yes[0];

     $string_asset = [];
     $string_asset['type'] = 'text';
     $string_asset['text'] = $txt;
     $string_asset['indx'] = random_string();

     $string_asset['conf'] = [];

     $string_asset['conf']['xpos'] = $xpos;
     $string_asset['conf']['ypos'] = $ypos;

     $string_asset['conf']['font'] = [];
     $string_asset['conf']['font']['space'] = $font_space;
     $string_asset['conf']['font']['size'] = $font_size;
     $string_asset['conf']['font']['weight'] = $font_weight;
     $string_asset['conf']['font']['lineHeight'] = $font_size;
     $string_asset['conf']['font']['family'] = $font_family;

     $string_asset['conf']['color'] = [];
     $string_asset['conf']['color']['cmyk'] = [];

     $string_asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($font_fill));
     $string_asset['conf']['opacity'] = '1';
     $string_asset['conf']['width'] = '2500';
     $string_asset['conf']['height'] = '2500';
     $string_asset['conf']['depth'] = '25000';
     $string_asset['conf']['unit'] = 'px';

     $res[]= $string_asset;
     return $res;
}

function extract_poly_assets($poly_nodes){
     $res = [];
     $idx = 0;
     foreach($poly_nodes as $node){
          $poly_asset = [];
          $poly_asset['type'] = 'poly';
          $poly_asset['indx'] = sprintf('poly_%s', $idx);
          $poly_asset['conf'] = [];
          $poly_asset['conf']['points'] = $node['attributes']['points'];
          $poly_asset['conf']['unit'] = 'px';
          $poly_asset['conf']['color'] = [];
          $poly_asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($node['attributes']['color']));
          $poly_asset['conf']['depth'] = '100';
          if(false != $node['slot']){
               $poly_asset['slot']['xpos'] = $node['xpos'];
               $poly_asset['slot']['ypos'] = $node['ypos'];
               $poly_asset['slot']['width'] = $node['width'];
               $poly_asset['slot']['height'] = $node['height'];
               $poly_asset['conf']['depth'] = '750';
          }
          $res[]= $poly_asset;
          $idx++;
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
     foreach($css as $css){
          $idx = array_search(sprintf('%s', $css), $coll[1]);
          if(false == $idx){ continue; }
          $statements = $coll[2][$idx];
          $statements = explode(';', $statements);
          foreach($statements as $statement){
               $ary = explode(':', $statement);
               if(null == $ary[0]){ continue;}
               $res[$ary[0]] = $ary[1];
          }
     }
// @file_put_contents('/tmp/out', json_encode($css, JSON_PRETTY_PRINT), FILE_APPEND);
// @file_put_contents('/tmp/out', json_encode($res, JSON_PRETTY_PRINT), FILE_APPEND);
     return $res;
}

