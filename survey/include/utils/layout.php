<?php defined('ABSPATH') || exit;

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
     // $res = get_posts(array('tag' => array($tags)));
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_layout' and post_title = '{$group}' order by ID desc limit 1;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_layout_by_group_and_rule($group, $rule){
     // $res = get_posts(array('tag' => array($tags)));
     $sql = <<<EOD
          select * from wp_posts 
               where post_type = 'surveyprint_layout' 
               and post_title = '{$group}' 
               and post_excerpt = '{$rule}' 
               order by ID desc
               limit 1
;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_layouts_by_tags($tags){
     $res = get_posts(array('tag' => array($tags)));
     $sql = <<<EOD
          select p.ID, t.term_id, t.name
               from wp_posts p, wp_term_relationships r, wp_terms t
               where p.post_type = 'surveyprint_layout'
               and r.term_taxonomy_id = t.term_id
               and p.ID = r.object_id
               and p.ID = 29823;
EOD;
     $sql = debug_sql($sql);
     $sql = <<<EOD
          select p.ID, p.post_title, t.name
               from wp_posts p, wp_term_relationships r, wp_terms t
               where p.post_type = 'surveyprint_layout'
               and r.term_taxonomy_id = t.term_id
               and p.ID = r.object_id
               and t.name like '%th%';

EOD;
     $sql = debug_sql($sql);
};

function get_layout_by_rule($rule){
     $rule = esc_sql($rule);
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_layout' and post_excerpt = '{$rule}' order by ID desc
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
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

function px_pump($px, $ppi_1st, $ppi_2nd){
     $ppi_1st = floatval($ppi_1st);
     $ppi_2nd = floatval($ppi_2nd);
     $res = floatval($px);
     $res /= $ppi_1st;
     $res *= $ppi_2nd;
     return $res;
}

function fit_image_asset_into_slot($doc, $asset){

     // $asset['src'] = 'http://127.0.0.1:8083/wp-content/plugins/nosuch/survey/asset/test.300.png';
     // $asset['src'] = WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.300.png';

     $temp = $asset['src'];
     $temp = remove_base_from_chunk($temp);
     $temp = preg_replace('/\s+/', '', $temp);

// whether or not chunk is of type base64 
     $vali = base64_decode($temp, true);
     $ilav = base64_encode($vali);
     $size = null;

// asset is base64 chunk 
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
          [ 'tokens'=>['American', 'Typewriter'], 'res'=>'American Typewriter' ]
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
