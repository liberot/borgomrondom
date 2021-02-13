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



