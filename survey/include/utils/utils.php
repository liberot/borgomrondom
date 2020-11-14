<?php defined('ABSPATH') || exit;

function random_string($length=21){
     return substr(
          str_shuffle(
               str_repeat(
                    $x='abcdefghijklmnopqrstuvwxyz0123456789',
                    ceil($length/strlen($x))
               )
          ), 1, $length
     );
}

function policy_match($policies){
     $res = false;
     foreach($policies as $policy){
          if(current_user_can($policy)){
               $res = true;
               return $res;
          }
     }
     return $res;
}

function dload($url){
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
     $res = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     $res = curl_exec($ch);
     curl_close($ch);
     return $res; 
}

function fetch($url, $token){
     $auth = "Authorization: Bearer ".$token;
     $post = json_encode([]);
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $auth));
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, POSTFIELDS, $post);
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
     $res = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     $res = curl_exec($ch);
     curl_close($ch);
     return $res; 
}

// add_action('wp_logout','ps_redirect_after_logout');
function ps_redirect_after_logout(){
     $referrer = $_SERVER['HTTP_REFERER'];
         wp_redirect($referrer);
         exit();
}

// add_action('wp_login_failed', 'my_front_end_login_fail');
function my_front_end_login_fail($username) {
     $referrer = $_SERVER['HTTP_REFERER'];
     if(!empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
          // wp_redirect( $referrer . '?login=failed' );
          wp_redirect($referrer);
          exit();
     }
}

// add_filter('authenticate', 'blank_username_password', 1, 3);
function blank_username_password($user, $username, $password) {
     $referrer = $_SERVER['HTTP_REFERER'];
     $login_page = 'LOGIN_PAGE_URL';
     if($username == '' || $password == '' ) {
          wp_redirect($referrer);
          exit();
     }
}

function trim_incoming_numeric($val){
     $val = substr($val, 0, 15);
     $val = preg_replace('/[^0-9]/', '', $val);
     return $val;
}

// wp_check_invalid_utf8();
// wp_strip_all_tags();
function trim_incoming_filename($val){
     $val = substr($val, 0, 128);
     $val = sanitize_textarea_field($val);
     $val = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $val);
     return $val;
}

function trim_incoming_string($val){
     $val = substr($val, 0, 1024 *8);
     $val = sanitize_textarea_field($val);
     return $val;
}

function walk_the_doc($doc){
     $res = null;
     if(is_object($doc)){ $doc = get_object_vars($doc); }
     if(false == is_array($doc)){ return false; }
     $res = trim_doc_node($doc);
     return $res;
}

function trim_doc_node($node){
     if(is_array($node)){
          foreach($node as $key=>$value){
               if(is_array($value)){
                    $node[$key] = trim_doc_node($value);
                    continue;
               }
               if(is_object($value)){
                    $value = get_object_vars($value);
                    $node[$key] = trim_doc_node($value);
                    continue;
               }
               $node[$key] = trim_for_print($value);
          }
     }
     return $node;
}

function trim_for_print($string){
     $res = $string;
     $res = preg_replace('/\\+\"/', '“', $res);
     $res = preg_replace('/\\+\'/', '’', $res);
     $res = preg_replace('/\"/', '“', $res);
     $res = preg_replace('/\'/', '’', $res);
     $res = preg_replace('/\\n+/', "\n\r", $res);
     $res = stripslashes($res);
     return $res;
}

function insert_survey_client(){
     $role = add_role(
          'customer', __('Customer', 'survey')
     );
     $client = wp_insert_user([
          'user_email'=>'__deb__survey__email__',
          'user_pass'=>'__deb__survey__password__',
          'user_login'=>'__deb__survey__client__',
          'user_nicename'=>'__deb__survey__client__',
          'display_name'=>'__deb__survey__client__',
          'nickname'=>'__deb__survey__nickname__',
          'first_name'=>'__deb__survey__forename__',
          'last_name'=>'__deb__survey__name__',
          'description'=>'__deb__survey__description__',
          'rich_editing'=>'false',
          'use_ssl'=>'true',
          'user_activation_key'=>'activation_key',
          'role'=>'customer'
     ]);
     return $client;
}

function auth_survey_client(){
     $client = wp_signon([
               'user_login'=>'__deb__survey__client__',
               'user_password'=>'__deb__survey__password__',
               'remember'=>true
          ],
          true
     );
     return $client;
}

function pigpack($doc){
     $temp = json_encode($doc);
     if(null == $temp){ return false; }
     $temp = base64_encode($temp);
     if(null == $temp){ return false; }
     return $temp;
}

function pagpick($pack){
     if(null == $pack){ return false; }

     $temp = base64_decode($pack, true);

     if(null == $temp){ return false; }
     $temp = json_decode($temp, true);
     if(null == $temp){ return false; }
     return $temp;
}

function psuuid(){
     $res = sprintf('%s::%s', random_string(24), mktime()); 
     return $res;
}

function select_meta($coll){ 
     if(!is_null($coll)){
          foreach($coll as $item){
               $item->meta_input = get_post_meta($item->ID);
          }
     }
     return $coll;
}

function get_author_id(){
     return get_current_user_id();
}

function remove_base_from_chunk($chunk){
     $res = preg_replace('/data:image\/png;base64,/', '', $chunk);
     return $res;
}

function add_base_to_chunk($chunk){
     $res = remove_base_from_chunk($chunk);
     $res = sprintf('data:image/png;base64,%s', $res);
     return $res;
}

function set_session_var($key, $value){
     session_start();
     $_SESSION[$key] = $value;
}

function get_session_var($key){
     session_start();
     return $_SESSION[$key];
}

function debug_sql($sql){
     $sql = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $sql);
     $sql.= "\n"; 
     file_put_contents('/tmp/sql', $sql, FILE_APPEND);
     return $sql;
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
               $res = $pxs /( (2540 /100 /300) *$ppi);
               break;
          case 'mm':
               $res = $pxs /( (2540 /210 /300) *$ppi);
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
              $px = ( (2540 /100 /300) *$ppi) *val;
              break;
          case 'mm':
              $px = ( (2480 /210 /300) *$ppi) *val;
              break;
     }
     return $px;
}

// https://www.rapidtables.com/convert/color/rgb-to-cmyk.html
function rgb2cmyk($rgb){
     $res = ['c'=>0, 'm'=>0, 'y'=>0, 'k'=>1];
     if(null == $rgb){ return $res; }
     $res = [];
     $rrr = intval($rgb['r']) /255;
     $ggg = intval($rgb['g']) /255;
     $bbb = intval($rgb['b']) /255;
     $res['k'] = floatval(1 -max($rrr, $ggg, $bbb));
     $res['c'] = floatval((1 -$rrr -$res['k']) /(1 -$res['k']));
     $res['m'] = floatval((1 -$ggg -$res['k']) /(1 -$res['k']));
     $res['y'] = floatval((1 -$bbb -$res['k']) /(1 -$res['k']));
     return $res;
}

function hex2rgb($hex) {
     $color = str_replace('#','',$hex);
     $rgb = array(
          'r'=>hexdec(substr($color,0,2)),
          'g'=>hexdec(substr($color,2,2)),
          'b'=>hexdec(substr($color,4,2)),
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

function fit_image_asset_to_slot($asset){

     // $asset['src'] = 'http://127.0.0.1:8083/wp-content/plugins/nosuch/survey/asset/test.300.png';
     // $asset['src'] = WP_PLUGIN_DIR.SURVeY.DIRECTORY_SEPARATOR.'asset'.DIRECTORY_SEPARATOR.'test.300.png';

     $temp = $asset['src'];
     $temp = remove_base_from_chunk($temp);
     $temp = preg_replace('/\s+/', '', $temp);

     $vali = base64_decode($temp, true);
     $ilav = base64_encode($vali);
     $size = null;

     if($temp == $ilav){
          $temp = add_base_to_chunk($temp);
          $size = getimagesize('data://'.$temp);
     }
     else {
          if(@file_exists($asset['src'])){
               $temp = @file_get_contents($asset['src']);
          }
          else if(false != parse_url($asset['src'])){
               $temp = dload($asset['src']);
          }
          $temp = add_base_to_chunk(base64_encode($temp));
          $asset['src'] = $temp;
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
     $slot_width = floatval($asset['conf']['slotW']);
     $slot_height = floatval($asset['conf']['slotH']);
     $slot_x = floatval($asset['conf']['slotX']);
     $slot_y = floatval($asset['conf']['slotY']);
     $max_scale_ratio = floatval($asset['conf']['max_scale_ratio']);
     $scale_type = $asset['conf']['scale_type'];

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

               if('cut_into_slot' == $scale_type){
                    $r = $slot_height /$h;
                    if($r >= $max_scale_ratio){ 
                        $r = $max_scale_ratio; 
                    }
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

               if('cut_into_slot' == $scale_type){
                    $r = $slot_width /$width;
                    if($r >= $max_scale_ratio){ 
                        $r = $max_scale_ratio; 
                    }
                    $w = $width *$r;
                    $h = $height *$r;
               }

               $xoffset = ($slot_width -$w) /2;
               $yoffset = ($slot_height -$h) /2;
               break;
     }

     $asset['conf']['xpos'] = $slot_x +$xoffset; 
     $asset['conf']['ypos'] = $slot_x +$yoffset; 
     $asset['conf']['width'] = $w;
     $asset['conf']['height'] = $h; 
     $asset['conf']['xoffset'] = $xoffset;
     $asset['conf']['yoffset'] = $yoffset;
     $asset['conf']['scale'] = $r;
     $asset['conf']['layoutCode'] = $layout_code; 

     // print_r($asset['conf']);

     return $asset;
}
