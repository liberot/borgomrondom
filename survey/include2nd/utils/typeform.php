<?php defined('ABSPATH') || exit;



function insert_typeform_survey_from_descriptor($survey_file_name){

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

     $res = insert_typeform_survey($survey);
     $res = insert_typeform_groups($survey, $groups);
}



function insert_typeform_groups($survey, $groups){

     global $wpdb;

     $survey_ref = esc_sql($survey['id']);
     foreach($groups as $group){
          $ref = esc_sql($group['ref']);
          $parent_ref = esc_sql($group['parent_id']);
          $title = esc_sql($group['title']);

          $prefix = $wpdb->prefix;
               $sql = <<<EOD
                    insert into {$prefix}ts_bb_group
                    (ref, parent_ref, survey_ref, title, init) 
               values 
                    ('{$ref}', '{$parent_ref}', '{$survey_ref}', '{$title}', now())
EOD;
          $sql = debug_sql($sql);
          $res |= $wpdb->query($sql);
     }

     return $res;
}



function insert_typeform_survey($survey){

     global $wpdb;

     $ref = esc_sql($survey['id']);
     $title = esc_sql($survey['title']);
     $headline = esc_sql($survey['welcome']);

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_survey 
               (ref, title, headline, init) 
          values 
               ('{$ref}', '{$title}', '{$headline}', now())
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
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

