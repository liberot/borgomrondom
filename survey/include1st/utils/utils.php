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

function trim_incoming_string($val, $max=null){
     if(is_null($val)){ 
          $val = ''; 
     }
     if(is_null($max)){
          $max = 1024;
     }
     $val = substr($val, 0, 1024);
     $val = sanitize_text_field($val);
     return $val;
}

function trim_incoming_numeric($val){
     if(is_null($val)){ $val = 0; }
     $val = substr($val, 0, 15);
     $val = preg_replace('/[^0-9]/', '', $val);
     return $val;
}

// wp_check_invalid_utf8();
// wp_strip_all_tags();
function trim_incoming_filename($val){
     if(is_null($val)){ $val = ''; }
     $val = substr($val, 0, 128);
     $val = sanitize_textarea_field($val);
     $val = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $val);
     return $val;
}

function trim_incoming_book($toc){

     $res = null;

     if(false == is_array($toc)){ 
          return $res; 
     }

     $res = [];
     foreach($toc as $item){

          $thread_id = $item['threadId'];
          $thread_id = substr($thread_id, 0, 128);
          $thread_id = preg_replace('/[^A-Za-z0-9-]/', '', $thread_id);

          $section_id = $item['sectionId'];
          $section_id = substr($section_id, 0, 128);
          $section_id = preg_replace('/[^A-Za-z0-9-]/', '', $section_id);

          $group_ref = $item['groupRef'];
          $group_ref = substr($group_ref, 0, 128);
          $group_ref = preg_replace('/[^A-Za-z0-9-]/', '', $group_ref);

          $panel_ref = $item['panelRef'];
          $panel_ref = substr($panel_ref, 0, 128);
          $panel_ref = preg_replace('/[^A-Za-z0-9-]/', '', $panel_ref);

          $res[]= [
               'threadId'=>$thread_id,
               'sectionId'=>$section_id,
               'groupRef'=>$group_ref,
               'panelRef'=>$panel_ref
          ];
     }

     return $res;
}

function trim_incoming_conditions($coll){

     $res = null;

     if(false == is_array($coll)){ 
          return $res; 
     }

     $res = [];
     foreach($coll as $item){

          $section_id = substr($item['sectionId'], 0, 128);
          $section_id = preg_replace('/[^A-Za-z0-9-]/', '', $section_id);

          $panel_ref = substr($item['panelRef'], 0, 128);
          $panel_ref = preg_replace('/[^A-Za-z0-9-]/', '', $panel_ref);

          $key = substr($item['key'], 0, 128);
          $key = preg_replace('/[^A-Za-z0-9-]/', '', $key);

          $val = substr($item['key'], 0, 1024);
          $val = trim_incoming_string($item['val']);

          $res[]= [
               'sectionId'=>$section_id, 
               'panelRef'=>$panel_ref, 
               'key'=>$key, 
               'val'=>$val
          ];
     }

     return $res;
}

function trim_incoming_history($coll){

     $res = null;

     if(false == is_array($coll)){ 
          return $res; 
     }

     $res = [];
     foreach($coll as $item){

          $section_id = substr($item['sectionId'], 0, 128);
          $section_id = preg_replace('/[^A-Za-z0-9-]/', '', $section_id);

          $panel_ref = substr($item['panelRef'], 0, 128);
          $panel_ref = preg_replace('/[^A-Za-z0-9-]/', '', $panel_ref);

          $res[]= ['sectionId'=>$section_id, 'panelRef'=>$panel_ref];
     }

     return $res;
}

// todo
function trim_incoming_variables($coll){

     $res = null;

     if(false == is_array($coll)){ 
          return $res; 
     }

     $res = [];

     foreach($coll as $item){
// fixdiss
          $res[]= $item;
     }

     return $res;
}

function trim_incoming_hidden_fields($coll){

     $res = null;

     if(false == is_array($coll)){ 
          return $res; 
     }

     $res = [];
     foreach($coll as $item){

          $section_id = substr($item['sectionId'], 0, 128);
          $section_id = preg_replace('/[^A-Za-z0-9-]/', '', $section_id);

          $panel_ref = substr($item['panelRef'], 0, 128);
          $panel_ref = preg_replace('/[^A-Za-z0-9-]/', '', $panel_ref);

          $field_ref = substr($item['fieldRef'], 0, 128);
          $field_ref = preg_replace('/[^A-Za-z0-9-\s]/', '', $field_ref);

          $field_title = substr($item['fieldTitle'], 0, 128);
          $field_title = preg_replace('/[^A-Za-z0-9-]/', '', $field_title);

          $res[]= [
               'sectionId'=>$section_id, 
               'panelRef'=>$panel_ref, 
               'fieldRef'=>$field_ref, 
               'fieldTitle'=>$field_title
          ];
     }

     return $res;
}

function walk_the_doc($doc){
     $res = null;
     if(is_object($doc)){ $doc = get_object_vars($doc); }
     if(false == is_array($doc)){ return res; }
     $res = trim_doc_node($doc);
     return $res;
}

function trim_doc_node($node){
     if(!is_array($node)){
          return $node;
     }
     foreach($node as $key=>$value){
          if(is_object($value)){
               $value = get_object_vars($value);
          }
          if(is_array($value)){
               $node[$key] = trim_doc_node($value);
               continue;
          }
          $node[$key] = trim_for_print($value);
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
/*
     $sql = <<<EOD
          delete from wp_users where user_login = 'surveyprint'
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
*/

// sets up a guest client
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
     // he who cannot save and does not exist makin trouble no need as for debug
     set_session_ticket('unique_guest', random_string(64));
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

function get_author_id(){
     $res = get_current_user_id();
     return $res;
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

// https://wordpress.org/support/plugin/wp-session-manager/
function set_session_ticket($key, $value, $force=false){
     if(function_exists('wp_session_start')){ 
          wp_session_start();
          global $wp_session;
          $wp_session[$key] = $value;
          return true;
     }
     session_start();
     if(true == $force){
          $_SESSION[$key] = $value;
          return true;
     }
     return true;
}

// https://wordpress.org/support/plugin/wp-session-manager/
function get_session_ticket($key){
     if(function_exists('wp_session_start')){ 
          wp_session_start();
          global $wp_session;
          return $wp_session[$key]; 
     }
     session_start();
     return $_SESSION[$key];
}

function debug_sql($sql){

     if(true != Proc::TMP_WRITE_SQL){
          return $sql;
     }

     $sql = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $sql);
     $sql.= "\n"; 
     @file_put_contents('/tmp/sql', $sql, FILE_APPEND);

     return $sql;
}

// add_action('init', 'init_survey_guest');
function init_survey_guest(){
     if(is_null(get_session_ticket('unique_guest'))){
          set_session_ticket('unique_guest', random_string(128), true);
     }
     if(true != is_user_logged_in()){
          $res = auth_guest_client();
          set_session_ticket('unique_guest', random_string(128), true);
     }
}


