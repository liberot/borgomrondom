<?php defined('ABSPATH') || exit;



function walk_the_doc($doc){
     $res = null;
     if(is_object($doc)){ 
          $doc = get_object_vars($doc); 
     }
     if(false == is_array($doc)){ 
          return res; 
     }
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



function random_string($length=21){
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



function psuuid(){
     $res = sprintf('%s::%s', random_string(25), mktime()); 
     return $res;
}



function get_author_id(){
     $res = get_current_user_id();
     return $res;
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



function trim_incoming_filename($val){
     if(is_null($val)){ $val = ''; }
     $val = substr($val, 0, 128);
     $val = sanitize_textarea_field($val);
     $val = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $val);
     return $val;
}



function insert_survey_page(){

     delete_survey_page();

     $conti = <<<EOD
        <p>[client_view]</p>
EOD;

     $page_id = wp_insert_post([
          'post_author'=>get_author_id(),
          'post_content'=>$conti,
          'post_title'=>'Questionnaire',
          'post_status'=>'publish',
          'comment_status'=>'closed',
          'ping_status'=>'closed',
          'post_name'=>'bookbuilder',
          'post_type'=>'page',
          'comment_count'=>0
     ]);

     return $page_id;
}



function delete_survey_page(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          delete from {$prefix}posts 
               where post_type = 'page' 
               and post_title = 'Questionnaire' 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;

}



