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
     $choice = parse_choices($doc['fields'], null, null);

     $res = insert_survey($survey, $data);
     $res = insert_groups($survey, $groups);
     $res = insert_fields($survey, $fields);
     $res = insert_choice($survey, $choice);
}



function insert_choice($survey, $choice){

     global $wpdb;

     $survey_ref = esc_sql($survey['id']);
     $pos = 0;
     foreach($choice as $ch){

          $ref = esc_sql($ch['ref']);
          $typeform_ref = esc_sql($ch['id']);
          $parent_ref = esc_sql($ch['parent_ref']);
          $group_ref = esc_sql($ch['parent_ref']);
          $field_ref = esc_sql($ch['field_ref']);
          $title = esc_sql($ch['title']);
          $description = esc_sql($ch['description']);
          $label = esc_sql($ch['label']);
          $doc = base64_encode(json_encode($ch));

          $prefix = $wpdb->prefix;

          $sql = <<<EOD
               insert into {$prefix}ts_bb_choice
                    (
                         ref, 
                         typeform_ref, 
                         survey_ref,
                         group_ref, 
                         parent_ref, 
                         field_ref, 
                         title, 
                         description, 
                         doc, 
                         pos,
                         init
                    )
               values 
                    (
                         '{$ref}', 
                         '{$typeform_ref}', 
                         '{$survey_ref}', 
                         '{$group_ref}', 
                         '{$parent_ref}', 
                         '{$field_ref}', 
                         '{$label}', 
                         '{$description}', 
                         '{$doc}', 
                         '{$pos}',
                         now() 
                    )
EOD;

          $sql = debug_sql($sql);

          $res |= $wpdb->query($sql);
          $pos = $pos +1;
     }

     return $res;
}



function insert_fields($survey, $fields){

     global $wpdb;

     $survey_ref = esc_sql($survey['id']);
     $pos = 0;
     foreach($fields as $field){

          $ref = esc_sql($field['ref']);
          $typeform_ref = esc_sql($field['id']);
          $parent_ref = esc_sql($field['parent_ref']);
          $group_ref = esc_sql($field['parent_ref']);
          $type = esc_sql($field['type']);
          $title = esc_sql($field['title']);
          $description = '';
          if(!is_null($field['properties']['description'])){
               $description = esc_sql($field['properties']['description']);
          }
          $doc = esc_sql($field['doc']);

          $prefix = $wpdb->prefix;

          $sql = <<<EOD
               insert into {$prefix}ts_bb_field
                    (
                         ref, 
                         typeform_ref, 
                         parent_ref, 
                         group_ref, 
                         survey_ref, 
                         title, 
                         description, 
                         type, 
                         doc, 
                         pos,
                         init
                    )
               values 
                    (
                         '{$ref}', 
                         '{$typeform_ref}', 
                         '{$parent_ref}', 
                         '{$group_ref}', 
                         '{$survey_ref}', 
                         '{$title}', 
                         '{$description}', 
                         '{$type}', 
                         '{$doc}', 
                         '{$pos}',
                         now() 
                    )
EOD;
          $sql = debug_sql($sql);

          $res |= $wpdb->query($sql);
          $pos = $pos +1;
     }

     return $res;
}



function insert_groups($survey, $groups){

     global $wpdb;

     $survey_ref = esc_sql($survey['id']);
     foreach($groups as $group){

          $ref = esc_sql($group['ref']);
          $parent_ref = esc_sql($group['parent_ref']);
          $typeform_ref = esc_sql($group['id']);
          $title = esc_sql($group['title']);
          $doc = esc_sql($group['doc']);

          $prefix = $wpdb->prefix;
               $sql = <<<EOD
               insert into {$prefix}ts_bb_group
                    (ref, typeform_ref, parent_ref, survey_ref, title, init, doc) 
               values 
                    ('{$ref}', '{$typeform_ref}', '{$parent_ref}', '{$survey_ref}', '{$title}', now(), '{$doc}')
EOD;
          $sql = debug_sql($sql);
          $res |= $wpdb->query($sql);
     }

     return $res;
}



function insert_survey($survey, $data){

     global $wpdb;

     $ref = esc_sql($survey['id']);
     $title = esc_sql($survey['title']);
     $headline = esc_sql($survey['welcome']);
     $doc = esc_sql(base64_encode($data));

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_survey 
               (ref, title, headline, init, doc) 
          values 
               ('{$ref}', '{$title}', '{$headline}', now(), '{$doc}')
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



function parse_groups($fields, $parent_ref, $res){

     if(is_null($fields)){ return $res; }
     if(is_null($parent_ref)){ $parent_ref = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($fields as $field){

          $field['parent_ref'] = $parent_ref;
          $childs = $field['properties']['fields'];
          if(is_null($childs)){

          }
          else {
               $group = [];
               $group['id'] = $field['id'];
               $group['ref'] = $field['ref'];
               $group['title'] = $field['title'];
               $group['type'] = $field['type'];
               $group['parent_ref'] = $parent_ref;
               $group['doc'] = base64_encode(json_encode($field));
               $res[]= $group;
               $parent_ref = $field['ref'];
               $res = parse_groups($childs, $parent_ref, $res);
          }
     }

     return $res;
}



function parse_fields($fields, $parent_ref, $res){

     if(is_null($fields)){ return $res; }
     if(is_null($parent_ref)){ $parent_ref = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($fields as $field){

          $field['parent_ref'] = $parent_ref;
          $field['group_ref'] = $parent_ref;
          $childs = $field['properties']['fields'];

          if(is_null($childs)){
               $field['doc'] = base64_encode(json_encode($field));
               $res[]= $field;
          }
          else {
               $parent_ref = $field['ref'];
               $res = parse_fields($childs, $parent_ref, $res);
          }
     }

     return $res;
}



function parse_choices($fields, $parent_ref, $res){

     if(is_null($fields)){ return $res; }
     if(is_null($parent_ref)){ $parent_ref = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($fields as $field){

          $properties = $field['properties'];
          $childs = $field['properties']['fields'];
          $choices = $field['properties']['choices'];
          if(is_null($childs)){
               if(!is_null($choices)){
                    foreach($choices as $choice){
                         $choice['parent_ref'] = $parent_ref;
                         $choice['typeform_ref'] = $field['id'];
                         $choice['field_ref'] = $field['ref'];
                         $res[]= $choice;
                    }
               }
          }
          else {
               $parent_ref = $field['ref'];
               $res = parse_choices($childs, $parent_ref, $res);
          }
     }

     return $res;
}

