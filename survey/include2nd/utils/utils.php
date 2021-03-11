<?php defined('ABSPATH') || exit;



function bb_walk_the_doc($doc){
     $res = null;
     if(is_object($doc)){ 
          $doc = get_object_vars($doc); 
     }
     if(false == is_array($doc)){ 
          return res; 
     }
     $res = bb_trim_doc_node($doc);
     return $res;
}



function bb_trim_doc_node($node){
     if(!is_array($node)){
          return $node;
     }
     foreach($node as $key=>$value){
          if(is_object($value)){
               $value = get_object_vars($value);
          }
          if(is_array($value)){
               $node[$key] = bb_trim_doc_node($value);
               continue;
          }
          $value = false === $value ? 'false' : $value;
          $value = true === $value ? 'true' : $value;
          $node[$key] = bb_trim_for_print($value);
     }
     return $node;
}



function bb_trim_for_print($string){

     $res = $string;

     $res = preg_replace('/\\+\"/', '“', $res);
     $res = preg_replace('/\\+\'/', '’', $res);
     $res = preg_replace('/\"/', '“', $res);
     $res = preg_replace('/\'/', '’', $res);
     $res = preg_replace('/\\n+/', "\n\r", $res);
     $res = preg_replace('/\\\{0,}/', "", $res);

     $res = stripslashes($res);

     return $res;
}



function bb_get_random_string($length=21){
     return substr(
          str_shuffle(
               str_repeat(
                    $x = 'abcdefghijklmnopqrstuvwxyz0123456789',
                    ceil($length /strlen($x))
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



function bb_get_psuuid(){
     $res = sprintf('%s::%s', bb_get_random_string(25), mktime()); 
     return $res;
}



function bb_get_author_id(){
     $res = get_current_user_id();
     return $res;
}



function bb_trim_incoming_string($val, $max=null){
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



function bb_trim_incoming_numeric($val){
     if(is_null($val)){ $val = 0; }
     $val = substr($val, 0, 15);
     $val = preg_replace('/[^0-9]/', '', $val);
     return $val;
}



function bb_trim_incoming_filename($val){
     if(is_null($val)){ $val = ''; }
     $val = substr($val, 0, 128);
     $val = sanitize_textarea_field($val);
     $val = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $val);
     return $val;
}



function bb_insert_survey_page(){

     $post_content = <<<EOD
        <p>[bb_client_view]</p>
EOD;

     $page_id = wp_insert_post([
          'post_author'=>bb_get_author_id(),
          'post_content'=>$post_content,
          'post_title'=>'BookBuilder',
          'post_status'=>'publish',
          'comment_status'=>'closed',
          'ping_status'=>'closed',
          'post_name'=>'bookbuilder',
          'post_type'=>'page',
          'comment_count'=>0
     ]);

     return $page_id;
}



function bb_delete_survey_page(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          delete from {$prefix}posts 
               where post_type = 'page' 
               and post_name = 'bookbuilder' 
EOD;
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



// https://wordpress.org/support/plugin/wp-session-manager/
function bb_set_session_ticket($key, $value){
     if(function_exists('wp_session_start')){ 
          wp_session_start();
          global $wp_session;
          $wp_session[$key] = $value;
          return true;
     }
     session_start();
     $_SESSION[$key] = $value;
     return true;
}



// https://wordpress.org/support/plugin/wp-session-manager/
function bb_get_session_ticket($key){
     if(function_exists('wp_session_start')){ 
          wp_session_start();
          global $wp_session;
          return $wp_session[$key]; 
     }
     session_start();
     return $_SESSION[$key];
}



function bb_insert_guest_client(){

// sets up a guest client
// client is cutomer as for debug reasons
     // $role_title = Role::GUEST;
     $role_title = Role::CUSTOMER;
     $role = add_role($role_title, 'BookBuilder Client');
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



function bb_add_debug_field($key, $value){

     if(is_null($_SESSION['debug'])){
          $_SESSION['debug'] = [];
     }
     if(is_null($_SESSION['debug'][$key])){
          $_SESSION['debug'][$key] = [];
     }
     $_SESSION['debug'][$key][]= $value;
}



function bb_flush_debug_field(){

     if(is_null($_SESSION['debug'])){
          return;
     }

     foreach($_SESSION['debug'] as $key=>$value){
          print '<br/>--<br/>';
          print $key;
          print '<br/>--<br/>';
          print_r($value);
          print '<br/>--<br/>';
     }
     $_SESSION['debug'] = [];
}



function bb_remove_base_from_chunk($chunk){
     $res = preg_replace('/data:image\/png;base64,/', '', $chunk);
     return $res;
}



function bb_add_base_to_chunk($chunk){
     $res = bb_remove_base_from_chunk($chunk);
     $res = sprintf('data:image/png;base64,%s', $res);
     return $res;
}



function bb_trim_incoming_hidden_fields($coll){
     $res = [];
     foreach($coll as $field){
          $key = bb_trim_for_print(bb_trim_incoming_string($field['key'], 255));
          $val = bb_trim_for_print(bb_trim_incoming_string($field['val'], 1024));
          $res[]= ['key'=>$key, 'val'=>$val];
     }
     return $res;
}




