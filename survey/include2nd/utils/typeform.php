<?php defined('ABSPATH') || exit;



function insert_typeform_surveys(){

     $res = false;

     $files = read_typeform_json_descriptors();
     foreach($files as $file){

          $res = insert_typeform_survey_from_descriptor($file);
     }

     return $res;
}



function insert_typeform_survey_from_descriptor($survey_file_name){

     $res = false;

     $path = sprintf('%s/%s', Path::get_typeform_dir(), $survey_file_name);

     $data = @file_get_contents($path);
     if(is_null($data)){ 
          return $res; 
     }

     $doc = json_decode($data);
     if(is_null($doc)){ 
          return $res; 
     }

     $doc = walk_the_doc($doc);

     $survey = parse_survey($doc);
     $groups = parse_groups($doc['fields'], null, null);
     $fields = parse_fields($doc['fields'], null, null);
     $choices = parse_choices($doc['fields'], null, null);
     $actions = parse_actions($doc['logic'], null, null);

     $res = insert_survey($survey, $data);
     $res&= insert_groups($survey, $groups);
     $res&= insert_fields($survey, $fields);
     $res&= insert_choices($survey, $choices);
     $res&= insert_actions($survey, $actions);

     return $res;
}



function insert_actions($survey, $actions){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $survey_ref = esc_sql($survey['id']);

     foreach($actions as $action){

          $ref = esc_sql($action['ref']);
          $field_ref = esc_sql($action['field_ref']);
          $type = esc_sql($action['type']);
          $cmd = esc_sql($action['cmd']);
          $link_type = esc_sql($action['link_type']);
          $link_ref = esc_sql($action['link_ref']);
          $doc = esc_sql(base64_encode(json_encode($action['condition'])));

          $sql = <<<EOD
               insert into {$prefix}ts_bb_action
                    (
                         ref, 
                         survey_ref,
                         field_ref,
                         type,
                         cmd,
                         link_type,
                         link_ref,
                         doc,
                         init
                    )
               values 
                    (
                         '{$ref}',
                         '{$survey_ref}',
                         '{$field_ref}',
                         '{$type}',
                         '{$cmd}',
                         '{$link_type}',
                         '{$link_ref}',
                         '{$doc}',
                         now()
                    )

EOD;

          $sql = debug_sql($sql);
          $res = $wpdb->query($sql);
     }

     return $res;
}




function insert_choices($survey, $choices){

     $res = false;

     global $wpdb;

     $survey_ref = esc_sql($survey['id']);
     $prefix = $wpdb->prefix;
     $pos = 0;
     foreach($choices as $choice){

          $ref = esc_sql($choice['ref']);
          $typeform_ref = esc_sql($choice['id']);
          $parent_ref = esc_sql($choice['parent_ref']);
          $group_ref = esc_sql($choice['parent_ref']);
          $field_ref = esc_sql($choice['field_ref']);
          $title = esc_sql($choice['title']);
          $description = esc_sql($choice['description']);
          $label = esc_sql($choice['label']);
          $doc = esc_sql(base64_encode(json_encode($choice)));

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

          $res = $wpdb->query($sql);
          $pos = $pos +1;
     }

     return $res;
}



function insert_fields($survey, $fields){

     $res = false;

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

          $res = $wpdb->query($sql);
          $pos = $pos +1;
     }

     return $res;
}



function insert_groups($survey, $groups){

     $res = false;

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
          $res = $wpdb->query($sql);
     }

     return $res;
}



function insert_survey($survey, $data){

     $res = false;

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



function parse_actions($logics){

     $res = [];

     if(is_null($logics)){ return $res; }
     if(is_null($parent_ref)){ $parent_ref = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($logics as $logic){
          $type = $logic['type'];
          $field_ref = $logic['ref'];

          foreach($logic['actions'] as $action){
               $temp = [];
               $temp['cmd'] = $action['action'];
               if('jump' != $temp['cmd']){
                    continue;
               }
               $temp['ref'] = random_string(64);
               $temp['type'] = $type;
               $temp['field_ref'] = $field_ref;
               $temp['cmd'] = $action['action'];
               $temp['link_type'] = $action['details']['to']['type'];
               $temp['link_ref'] = $action['details']['to']['value'];
               $temp['condition'] = $action['condition'];
               $res[]= $temp;
          }
     }

     return $res;
}



function read_typeform_json_descriptors(){

     $files = [];

     $path = Path::get_typeform_dir();
     $h = opendir($path);
     if(is_null($h)){ return $files; }
     while(false !== ($file = readdir($h))){
          if($file != '.' && $file != '..'){
               preg_match('/(.json$)/', $file, $mtch);
               if(!empty($mtch)){
                    $files[]= $file;
               }
          }
     }
     closedir($h);

     return $files;
}



function get_typeform_surveys(){

     global $wpdb;

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_survey
EOD;

     $res = $wpdb->get_results($sql);

     return $res;
}



function get_survey_by_ref($ref) {

     $res = [];
     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;


// surveys
     $sql = <<<EOD
          select * from {$prefix}ts_bb_survey where ref = '{$ref}'
EOD;
     $res['survey'] = $wpdb->get_results($sql);



// groups
     $sql = <<<EOD
          select * from {$prefix}ts_bb_group where survey_ref = '{$ref}'
EOD;
     $res['groups'] = $wpdb->get_results($sql);



// fields
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field where survey_ref = '{$ref}'
          order by pos
EOD;
     $res['fields'] = $wpdb->get_results($sql);



// choices
     foreach($res['fields'] as $field){

          $field_ref = $field->ref;
          $field->choices = [];
          $sql = <<<EOD
               select * from {$prefix}ts_bb_choice where field_ref = '{$field_ref}'
               order by pos
EOD;
          $field->choices = $wpdb->get_results($sql);
     }

     return $res;

}


function set_target_survey($choice_ref, $target_survey_ref){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          update {$prefix}ts_bb_choice set target_survey_ref = '{$target_survey_ref}' where ref = '{$choice_ref}';
EOD;
     $res = $wpdb->query($sql);
     return $res;
}



function get_kickoff_field() {

     global $wpdb;
     $prefix = $wpdb->prefix;
     $title = Proc::KICKOFF_SURVEY_TITLE;
     $sql = <<<EOD
          select f.* from {$prefix}ts_bb_survey s, {$prefix}ts_bb_field f 
               where s.ref = f.survey_ref 
               and s.title like '%{$title}%' 
               order by pos 
               limit 1; 
EOD;
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_field_by_ref($ref) {

     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
          where ref = '{$ref}' 
EOD;
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_actions_of_field_by_ref($ref){

     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_action 
          where field_ref = '{$ref}' 
EOD;
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_field_of_survey_at_pos($survey_ref, $pos){

     $survey_ref = esc_sql($survey_ref);
     $pos = esc_sql($pos);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
          where survey_ref = '{$survey_ref}' 
          and pos = '{$pos}' 
EOD;
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_rec_of_field($client_id, $thread_id, $field_ref){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_rec 
          where client_id = '{$client_id}' 
          and thread_id = '{$thread_id}' 
          and field_ref = '{$field_ref}' 
EOD;
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_choice_of_field($field_ref){

     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_choice 
          where field_ref = '{$field_ref}' 
EOD;
     $res = $wpdb->get_results($sql);
     return $res;
}



