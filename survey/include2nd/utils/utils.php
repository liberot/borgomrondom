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

