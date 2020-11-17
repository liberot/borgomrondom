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
     // curl_setopt($ch, POSTFIELDS, $post);
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

function trim_incoming_answer($val){
     $val = substr($val, 0, 128);
     $val = sanitize_textarea_field($val);
     $val = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $val);
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

function insert_guest_client(){
// deletes guest client
     $sql = <<<EOD
          delete from wp_users where user_login = 'surveyprint'
EOD;
// sets up a guest client
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
// client is cutomer as for debug reasons
     // $role_title = Role::GUEST;
     $role_title = Role::CUSTOMER;
     $role = add_role($role_title, 'SurveyPrint Client');
     $client = wp_insert_user([
          'user_email'=>'surveyprint',
          'user_pass'=>'surveyprint',
          'user_login'=>'surveyprint',
          'user_nicename'=>'surveyprint',
          'display_name'=>'surveyprint',
          'nickname'=>'surveyprint',
          'first_name'=>'surveyprint',
          'last_name'=>'surveyprint',
          'description'=>'surveyprint',
          'rich_editing'=>'false',
          'use_ssl'=>'true',
          'user_activation_key'=>'surveyprint',
          'role'=>$role_title
     ]);
     return $client;
}

function auth_guest_client(){
     // he who cannot save and does not exist makin trouble no need 
     $client = wp_signon(
          [
               'user_login'=>'surveyprint',
               'user_password'=>'surveyprint',
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

function set_session_ticket($key, $value, $force=false){
     session_start();
     if(true == $force){
          $_SESSION[$key] = $value;
          return true;
     }
     if(false == is_null($_SESSION['$key'])){
          $_SESSION[$key] = $value;
     }
     return true;
}

function get_session_ticket($key){
     session_start();
     return $_SESSION[$key];
}

function debug_sql($sql){
     $sql = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $sql);
     $sql.= "\n"; 
     file_put_contents('/tmp/sql', $sql, FILE_APPEND);
     return $sql;
}

