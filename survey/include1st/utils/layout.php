<?php if(true != defined('ABSPATH')){ exit(); };

add_action('init', 'init_layout_utils');
function init_layout_utils(){

     $res = register_post_type(
          'surveyprint_layout',  [
               'label'                  =>'SurveyPrint Layout',
               'description'            =>'SurveyPrint Layout',
               'public'                 => false,
               'hierarchical'           => true,
               'exclude_from_search'    => true,
               'publicly_queryable'     => false,
               'show_ui'                => false,
               'show_in_menu'           => false,
               'show_in_nav_menus'      => false,
               'query_var'              => true,
               'rewrite'                => false,
               'capability_type'        => 'post',
               'has_archive'            => true,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );

     return $res;
}

function init_layout($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_layouts_by_group($group){

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_layout' and post_title = '{$group}' order by ID desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_layout',
          'post_title'=>$group,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_layout_by_group_and_rule($group, $rule){

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* 
               from {$prefix}posts 
               where post_type = 'surveyprint_layout' 
               and post_title = '{$group}' 
               and post_excerpt = '{$rule}' 
               order by ID desc
               limit 1
;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_layout',
          'post_title'=>$group,
          'post_excerpt'=>$rule,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_layouts_by_tags($tags){
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select p.ID, t.term_id, t.name
               from {$prefix}posts p, {$prefix}term_relationships r, {$prefix}terms t
               where p.post_type = 'surveyprint_layout'
               and r.term_taxonomy_id = t.term_id
               and p.ID = r.object_id
               and p.ID = 29823;
EOD;
     $sql = debug_sql($sql);
     $sql = <<<EOD
          select p.ID, p.post_title, t.name
               from {$prefix}posts p, 
               {$prefix}term_relationships r, {$prefix}terms t
               where p.post_type = 'surveyprint_layout'
               and r.term_taxonomy_id = t.term_id
               and p.ID = r.object_id
               and t.name like '%th%';

EOD;
     $sql = debug_sql($sql);
};

function get_layout_by_rule($rule){
     $rule = esc_sql($rule);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_layout' and post_excerpt = '{$rule}' order by ID desc
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_layout',
          'post_excerpt'=>$rule,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function px_to_unit($ppi, $pxs, $unit){

     if(is_null($ppi)){ $ppi = 300; }
     if(is_null($pxs)){ $pxs = 0; }
     if(is_null($unit)){ $unit = 'mm'; }

     $pxs = floatval($pxs);
     $ppi = floatval($ppi);
     $res = 0;

     switch($unit){
          case 'px':
               $res = $pxs;
               break;
          case 'inch':
               $res = $pxs /((2480 /(210 /25.4) /300) *$ppi);
               // $res = $pxs *$ppi;
               break;
          case 'mm':
               $res = $pxs /((2480 /(210 /1 ) /300) *$ppi);
               break;
     }

     return $res;
}

function unit_to_px($ppi, $val, $unit){

     if(is_null($ppi)){ $ppi = 300; }
     if(is_null($val)){ $val = 0; }
     if(is_null($unit)){ $unit = 'mm'; }

     $val = floatval($val);
     $px = 0;

     switch(unit){
          case 'px':
              $px = $val;
              break;
          case 'inch':
              $px = ((2480 /(210 /25.4) /300) *$ppi) *val;
              // $px = *$ppi *val;
              break;
          case 'mm':
              $px = ((2480 /(210 /1) /300) *$ppi) *val;
              break;
     }

     return $px;
}

// https://www.rapidtables.com/convert/color/rgb-to-cmyk.html
function rgb2cmyk($rgb){

     // $rgb = ['r'=>'0', 'g'=>'0', 'b'=>'0'];
     $res = ['c'=>0, 'm'=>0, 'y'=>0, 'k'=>1];
     if(null == $rgb){ return $res; }

     $rrr = intval($rgb['r']) /255;
     $ggg = intval($rgb['g']) /255;
     $bbb = intval($rgb['b']) /255;

     if(0 == $rrr && 0 == $ggg && 0 == $bbb){
          return $res;
     }

     $res = [];

     $res['k'] = floatval(1 -max($rrr, $ggg, $bbb));
     $res['c'] = floatval((1 -$rrr -$res['k']) /(1 -$res['k']));
     $res['m'] = floatval((1 -$ggg -$res['k']) /(1 -$res['k']));
     $res['y'] = floatval((1 -$bbb -$res['k']) /(1 -$res['k']));

     return $res;
}

function hex2rgb($hex) {

     $color = str_replace('#', '', $hex);

     $rgb = array(
          'r'=>hexdec(substr($color, 0, 2)),
          'g'=>hexdec(substr($color, 2, 2)),
          'b'=>hexdec(substr($color, 4, 2)),
     );

     return $rgb;
}

function rgb2hex($r, $g, $b){
     $res = sprintf('#%02x%02x%02x', $r, $g, $b);
     return $res;
}

function get_all_grey_like_colors(){
     $res = [];

     for($idx = 0; $idx < 255; $idx++){
          $res[]= rgb2hex($idx, $idx, $idx);
     }

     return $res;
}

function is_grey_hex($color){

      $res = false;
      $c = hex2rgb($color);

      if(255 > intval($c['b'])){
           if(0 < intval($c['b'])){
                if(intval($c['b']) == intval($c['r'])){
                     if(intval($c['b']) == intval($c['g'])){
                          $res = true;
                     }
                }
           }
     }

     return $res;
}

// green as for the textfield slots
// 009640ff
// r: 0 g: 150 b: 64
function is_green_hex($color){

     $res = false;
     $c = hex2rgb($color);

     if(0 == intval($c['r'])){
          if(150 == intval($c['g'])){
               if(64 == intval($c['b'])){
                    $res = true;
               }
          }
     }

     return $res;
}

function px_pump($px, $ppi_1st, $ppi_2nd){

     $ppi_1st = floatval($ppi_1st);
     $ppi_2nd = floatval($ppi_2nd);
     $res = floatval($px);
     $res /= $ppi_1st;
     $res *= $ppi_2nd;
     return $res;
}

function fit_image_asset_into_slot($doc, $asset){

     // $asset['src'] = 'http://127.0.0.1:8083/wp-content/plugins/bookbuilder/survey/asset/test.300.png';
     // $asset['src'] = Path::get_mock_dir().'/test.300.png';

     $temp = $asset['src'];
     $temp = remove_base_from_chunk($temp);
     $temp = preg_replace('/\s+/', '', $temp);

// whether or not chunk is of type base64 
     $vali = base64_decode($temp, true);
     $ilav = base64_encode($vali);

// asset is base64 chunk 
     $size = null;
     if($temp == $ilav){
          $temp = add_base_to_chunk($temp);
          $size = getimagesize('data://'.$temp);
     }
     else {
// asset is file resource
          if(@file_exists($asset['src'])){
               $temp = @file_get_contents($asset['src']);
          }
// asset is url resource
          else if(false != parse_url($asset['src'])){
               $temp = dload($asset['src']);
          }
          $temp = add_base_to_chunk(base64_encode($temp));
          $size = getimagesize('data://'.$temp);
     }

     if(null == $size){ return $asset; }

     $width = floatval($size[0]);
     $height = floatval($size[1]);
     $layout_code = $width >= $height ? 'L' : 'P';

     $asset['conf']['ow'] = $width;
     $asset['conf']['oh'] = $height;

     $r = 1;
     $w = $width;
     $h = $height;

     $xoffset = 0;
     $yoffset = 0;

// seems there is no layout to fit into
// ------------------------------------
     $asset['conf']['unit'] = 'px';
     if(null == $asset['conf']['slotW']){ $asset['conf']['slotW'] = $width; };
     if(null == $asset['conf']['slotH']){ $asset['conf']['slotH'] = $height; };
     if(null == $asset['conf']['slotX']){ $asset['conf']['slotX'] = 0; };
     if(null == $asset['conf']['slotY']){ $asset['conf']['slotY'] = 0; };
     if(null == $asset['conf']['scaleType']){ $asset['conf']['scaleType'] = 'no_scale'; };
     if(null == $asset['conf']['maxScaleRatio']){ $asset['conf']['maxScaleRatio'] = 1; };
// ------------------------------------

     $slot_width = floatval($asset['conf']['slotW']);
     $slot_height = floatval($asset['conf']['slotH']);
     $slot_x = floatval($asset['conf']['slotX']);
     $slot_y = floatval($asset['conf']['slotY']);
     $scale_type = $asset['conf']['scaleType'];
     $max_scale_ratio = floatval($asset['conf']['maxScaleRatio']);
     if(is_null($max_scale_ratio)){ $max_scale_ratio = 1; }

     switch($scale_type){

          case Layout::CUT_INTO_SLOT:
               $xr = $slot_width /$width;
               $yr = $slot_height /$height;
               $r = $xr >= $yr ? $xr : $yr;
               if($r >= $max_scale_ratio){ 
                   $r = $max_scale_ratio;
               }
               $w = $width *$r;
               $h = $height *$r;
               $xoffset = ($slot_width -$w) /2;
               $yoffset = ($slot_height -$h) /2;
               break;

          case Layout::NO_SCALE:
          default:
               switch($layout_code){
               case 'L':
                    if($width >= $slot_width){
                         $r = $slot_width /$width;
                         $w = $width *$r;
                         $h = $height *$r;
                    }
                    if($h >= $slot_height){
                         $r = $slot_height /$h;
                         $w = $width *$r;
                         $h = $height *$r;
                    }
                    $xoffset = ($slot_width -$w) /2;
                    $yoffset = ($slot_height -$h) /2;
                    break;

               case 'P':
                    if($height >= $slot_height){
                         $r = $slot_height /$height;
                         $w = $width *$r;
                         $h = $height *$r;
                    }
                    if($w >= $slot_width){
                         $r = $slot_width /$w;
                         $w = $width *$r;
                         $h = $height *$r;
                    }
                    $xoffset = ($slot_width -$w) /2;
                    $yoffset = ($slot_height -$h) /2;
                    break;
          }
     }

     $asset['conf']['xpos'] = $slot_x +$xoffset; 
     $asset['conf']['ypos'] = $slot_y +$yoffset; 
     $asset['conf']['width'] = $w;
     $asset['conf']['height'] = $h; 
     $asset['conf']['xoffset'] = $xoffset;
     $asset['conf']['yoffset'] = $yoffset;
     $asset['conf']['scale'] = $r;
     $asset['conf']['layoutCode'] = $layout_code; 

     return $asset;
}

function match_font_family($style){
     $font_family = $style['font-family'];
     $font_family = preg_replace('/\s+/i', '', $font_family);
     $font_family = preg_replace( '/\'/i', '', $font_family);
     $font_family = preg_replace( '/\"/i', '', $font_family);
     $font_family = preg_replace(  '/"/i', '', $font_family);
     $font_family = preg_replace(  "/'/i", '', $font_family);
     $res = $font_family;
     $font_family_map = [
          [ 'tokens'=>['American', 'Typewriter'], 'res'=>'American Typewriter' ],
          [ 'tokens'=>['Big', 'Booty', 'Bang'],   'res'=>'BigBooty' ],
          [ 'tokens'=>['Badang', 'Badei'],        'res'=>'BadangBadei' ]
     ];
     foreach($font_family_map as $map){
          $m = 0;
          foreach($map['tokens'] as $token){
               if(-1 != strpos($font_family, $token)){
                    $m++;
               }
          }
          if($m >= count($map['tokens'])){
               $res = $map['res'];
          }
     }
     return $res;
}

function match_font_weight($style){
     $font_family = $style['font-family'];
     $font_family = preg_replace('/\s+/i', '', $font_family);
     $font_family = preg_replace( '/\'/i', '', $font_family);
     $font_family = preg_replace( '/\"/i', '', $font_family);
     $font_family = preg_replace(  '/"/i', '', $font_family);
     $font_family = preg_replace(  "/'/i", '', $font_family);
     $font_weight = $style['font-weight'];
     if(-1 != strpos($font_family, 'Light')){
          $font_weight = 200;
     }
     $res = $font_weight;
     return $res;
}


function import_layouts(){
// reads layout svg fro the given rsloc
     $path = Path::get_layout_template_dir();

     if(!is_dir($path)){
          $message = esc_html(__('nothing to import', 'bookbuilder'));
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

     return $coll;
}


function parse_layout_doc($svg_path){

// grabs plain svg file
     $ldd = @file_get_contents($svg_path);
     if(null == $ldd){ 
          return false; 
     }

// sets up a parser
     $parser = xml_parser_create();

     xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
     xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
     xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
     xml_parse_into_struct($parser, trim($ldd), $svg_doc);
     xml_parser_free($parser);

// grabs mock spread json file
     $path = Path::get_mock_dir().'/mock-spread.json';
     $spread = @file_get_contents($path);
     if(null == $spread){ 
          return false; 
     }

// parses json 
     $doc = json_decode($spread);
     if(null == $doc){ 
          return false; 
     }

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

// polygon fields in grey is the image slots
     $res = eval_polygon_fields($svg_doc, $doc);

// insert of mock image assets
     if(true == Layout::INSERT_MOCK_IMAGE_ASSET){
          $res = insert_image_assets($doc, $res);
          $res = fit_image_assets_into_slot($doc, $res);
     }
     $doc['assets'] = array_merge($doc['assets'], $res);

// path fields as hearts and circls and such
// and then sometimes the image slots is paths too
     $res = eval_path_fields($svg_doc, $doc);

     if(true == Layout::INSERT_MOCK_IMAGE_ASSET){
          $res = insert_image_assets($doc, $res);
          $res = fit_image_assets_into_slot($doc, $res);
     }
     $doc['assets'] = array_merge($doc['assets'], $res);

// layout code is to be determined such as LPP -> landscape portrait portrait
     $doc['layout']['code'] = get_layout_code_of_spread($doc);

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

          $chunk = str_replace(',', ' ', $chunk);
          $chunk = str_replace(' ', ',', $chunk);

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

     $res = [];
     $idx = 0;
     $dpt = 0;

     foreach($svg_doc as $node){

// depth count as in layers of the svg doc
          $idx = $idx +1;

// evaluates paths
          if('path' != $node['tag']){
               continue;
          }

// css style attribute
          $css = $node['attributes']['style'];
          if(is_null($css)){ 
               continue; 
          }

          $style = get_style_coll_from_attribute($css);

          $color = $style['fill'];
          if(null == $color){
               continue; 
          }

// sets up an asset
          $asset = [];
          $asset['type'] = 'path';

// sets up a conf
          $asset['conf'] = [];
          $asset['slot'] = false; 
          $asset['textfield'] = false; 

          $asset['conf']['unit'] = $doc['unit'];
          $asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($color));

          $asset['indx'] = sprintf('path_%s', $idx);
          $asset['path'] = corr_path_d($node['attributes']['d'], $doc);

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

          $dpt = Layout::Z_STEP *$idx;

          if(is_grey_hex($color)){
               $asset['slot'] = true; 
          }

          $asset['conf']['depth'] = $dpt;
          $res[]= $asset;
     }

     return $res;
}

function eval_polygon_fields($svg_doc, $doc){

     $res = [];
     $idx = 0;
     $dpt = 0;

     foreach($svg_doc as $node){

// depth count of the svg layers
          $idx = $idx +1;

          if('polygon' != $node['tag']){
               continue;
          }

// evaluates the style af a polygon
          $css = $node['attributes']['style'];
          if(null == $css){
               continue;
          }

          $style = get_style_coll_from_attribute($css);

          $color = $style['fill'];
          if(is_null($color)){
               continue;
          }

// sets up an asset
          $asset = [];

          $asset['type'] = 'poly';
          $asset['indx'] = sprintf('poly_%s', intval($idx));

// sets up asset conf
          $asset['conf'] = [];

          $asset['conf']['unit'] = 'px';
          $asset['conf']['color'] = [];
          $asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb($color));

// points of the poly
          $points = $node['attributes']['points'];
          $points = trim(str_replace(',', ' ', $node['attributes']['points']));
          $points = explode(' ', $points);

          for($i = 0; $i < count($points); $i++){
               $points[$i] = corr_layout_pos($points[$i], $doc);
          }

// xpositions of a rect
          $xt = [];
          for($i = 0; $i < count($points); $i+= 2){ 
               $xt[]= $points[$i]; 
          }

// min und max x positions 
          sort($xt); $xmin = floatval($xt[0]);
          rsort($xt); $xmax = floatval($xt[0]);

// ypositions of a rect
          $yt = [];
          for($i = 1; $i < count($points); $i+= 2){ 
               $yt[]= $points[$i]; 
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
               $q +=$offset;
               $xtmp[]= $q;
               $s++;
          }

          $points = implode(' ', $xtmp);

          $asset['points'] = $points;

          $asset['conf']['xpos'] = floatval($xmin) +$doc['doc_x_offset'];
          $asset['conf']['ypos'] = floatval($ymin) +$doc['doc_y_offset'];
          $asset['conf']['width'] = $xmax -$xmin;
          $asset['conf']['height'] = $ymax -$ymin;

          $asset['slot'] = false;
          $asset['textfield'] = false; 

          $dpt = Layout::Z_STEP *$idx;

// whether asset is image slot or not
          if(is_grey_hex($color)){

               $asset['slot'] = true;

               $asset['layout_code'] = 'P';
               if(floatval($asset['conf']['width']) >= floatval($asset['conf']['height'])){ 
                    $asset['layout_code'] = 'L';
               }
          }

// whether asset is a text slot or not
          if(is_green_hex($color)){ 

               $asset['textfield'] = true; 

// mock data todo to be evaluated


// sets up an asset
               $asset['textfield'] = true;
               $asset['type'] = 'text';
               $asset['indx'] = sprintf('text_%s', $idx);
               $asset['text'] = get_random_words();
               $asset['style'] = $css;

// sets up asset conf
               $font_family = 'American Typewriter';
               $font_weight = floatval($cls['font-weight']);
               $font_size = 69;

               $asset['conf']['font'] = [];
               $asset['conf']['font']['family'] = $font_family;
               $asset['conf']['font']['size'] = $font_size;
               $asset['conf']['font']['lineHeight'] = floatval($font_size) *floatval(1.35);
               $asset['conf']['font']['align'] = 'block';
               $asset['conf']['font']['space'] = '1';
               $asset['conf']['font']['weight'] = $font_weight;

               $asset['conf']['unit'] = 'px';
               $asset['conf']['opacity'] = '1';
               $asset['conf']['color'] = [];
               $asset['conf']['color']['cmyk'] = rgb2cmyk(hex2rgb('#e74310'));
               $dpt = $dpt +50000;
          }

          $asset['conf']['depth'] = $dpt;
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

function get_layout_code_of_spread($doc){

     $res = '';
     foreach($doc['assets'] as $node){
          if(false != $node['slot']){
               $res = sprintf('%s%s', $res, $node['layout_code']);
          }
     }
     if('' == $res){ $res = 'U'; }

     return $res;
}

function insert_image_assets($doc, $nodes){

     $res = [];

     $landscape = @file_get_contents(Path::get_mock_dir().'/test.900.base');
     if(null == $landscape){ $landscape = 'missing landscape image locator'; }

     $portrait  = @file_get_contents(Path::get_mock_dir().'/test.p.base');
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

function get_random_words(){

     $res = [
          'Random Lines of Some No Sense 1st',
          'Random Lines of Some No Sense 2nd',
          'Random Lines of Some No Sense 3rd',
          'Random Lines of Some No Sense 4th',
          'Random Lines of Some No Sense 5th'
     ];

     $lines = @file(Path::get_random_words_path(), FILE_SKIP_EMPTY_LINES);

     if(is_null($lines)){

          return $res;
     }

     $res = [];
     $max = count($lines) -1;
     for($i = 0; $i < 10; $i++){
          $rnd = random_int(0, $max);
          $res[]= $lines[$rnd];
     }

     return $res;
}
