<?php defined('ABSPATH') || exit;



function insert_typeform_survey($survey_file_name){

     $path = sprintf('%s/%s', Path::get_typeform_dir(), $survey_file_name);

     $data = @file_get_contents($path);
     if(is_null($data)){ 
          return false; 
     }

     $doc = json_decode($data);
     if(is_null($doc)){ 
          return false; 
     }

     $doc = walk_the_doc($doc);

     $survey = parse_survey($doc);
     $groups = parse_groups($doc['fields'], null, null);
     $fields = parse_fields($doc['fields'], null, null);

}



function parse_survey($doc){

     $res = [];
     $res['id'] = $doc['id'];
     $res['type'] = $doc['type'];
     $res['title'] = $doc['title'];
     $res['welcome'] = $doc['welcome_screens'][0]['title'];
     return $res;
}



function parse_groups($fields, $parent_id, $res){

     if(is_null($fields)){ return $res; }
     if(is_null($parent_id)){ $parent_id = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($fields as $field){

          $field['parent_id'] = $parent_id;
          $childs = $field['properties']['fields'];
          if(is_null($childs)){

          }
          else {
               $group = [];
               $group['id'] = $field['id'];
               $group['ref'] = $field['ref'];
               $group['title'] = $field['title'];
               $group['type'] = $field['type'];
               $group['parent_id'] = $parent_id;
               $res[]= $group;
               $parent_id = $field['ref'];
               $res = parse_groups($childs, $parent_id, $res);
          }
     }

     return $res;
}



function parse_fields($fields, $parent_id, $res){

     if(is_null($fields)){ return $res; }
     if(is_null($parent_id)){ $parent_id = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($fields as $field){

          $field['parent_id'] = $parent_id;
          $childs = $field['properties']['fields'];
          if(is_null($childs)){
               $res[]= $field;
          }
          else {
               $parent_id = $field['ref'];
               $res = parse_fields($childs, $parent_id, $res);
          }
     }

     return $res;
}

