<?php defined('ABSPATH') || exit;



function bb_import_typeform_surveys(){

     $descriptors = bb_read_typeform_json_descriptors();

     foreach($descriptors as $descriptor){

          $res = bb_insert_typeform_survey_from_descriptor($descriptor);
     }

     return $res;
}



function bb_insert_typeform_survey_from_descriptor($descriptor){

     $path = $descriptor['path'];

     $data = @file_get_contents($path);
     if(is_null($data)){ 
          return $res; 
     }

     $doc = json_decode($data);
     if(is_null($doc)){ 
          return $res; 
     }

     $doc = bb_walk_the_doc($doc);

     $survey = bb_parse_survey($doc);
     $groups = bb_parse_groups($doc['fields'], null, null);
     $fields = bb_parse_fields($doc['fields'], null, null);
     $yesnos = bb_parse_yesnos($doc['fields'], null, null);

     $choices_of_no_choice = bb_parse_choices_of_no_choice($doc['fields'], null, null);

     $choices = bb_parse_choices($doc['fields'], null, null);
     $actions = bb_parse_actions($doc['logic'], null, null);

     $res = bb_insert_survey($descriptor, $survey, $data);
     $res&= bb_insert_groups($survey, $groups);
     $res&= bb_insert_fields($survey, $fields);

     $res&= bb_insert_choices($survey, $choices_of_no_choice);

     $res&= bb_insert_choices($survey, $yesnos);
     $res&= bb_insert_choices($survey, $choices);
     $res&= bb_insert_actions($survey, $actions);

     return $res;
}



function bb_insert_actions($survey, $actions){

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
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         now()
                    )
EOD;
          $sql = $wpdb->prepare($sql, $ref, $survey_ref, $field_ref, $type, $cmd, $link_type, $link_ref, $doc);
          $sql = bb_debug_sql($sql);
          $res = $wpdb->query($sql);
     }

     return $res;
}



function bb_insert_choices($survey, $choices){

     $res = null;

     global $wpdb;

     $survey_ref = esc_sql($survey['id']);
     $prefix = $wpdb->prefix;
     $pos = 0;
     if(is_null($choices)){
          return $res;
     }
     foreach($choices as $choice){

          $ref = esc_sql($choice['ref']);
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
                         '%s', 
                         '%s', 
                         '%s', 
                         '%s', 
                         '%s', 
                         '%s', 
                         '%s', 
                         '%s', 
                         '%s',
                         now() 
                    )
EOD;
          $sql = $wpdb->prepare($sql, $ref, $survey_ref, $group_ref, $parent_ref, $field_ref, $label, $description, $doc, $pos);
          $sql = bb_debug_sql($sql);
          $res = $wpdb->query($sql);
          $pos = $pos +1;
     }

     return $res;
}



function bb_insert_fields($survey, $fields){

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
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         '%s',
                         now()
                    )
EOD;
          $sql = $wpdb->prepare($sql, $ref, $typeform_ref, $parent_ref, $group_ref, $survey_ref, $title, $description, $type, $doc, $pos);
          $sql = bb_debug_sql($sql);
          $res = $wpdb->query($sql);
          $pos = $pos +1;
     }

     return $res;
}



function bb_insert_groups($survey, $groups){

     $res = false;

     global $wpdb;

     $survey_ref = esc_sql($survey['id']);
     $prefix = $wpdb->prefix;
     foreach($groups as $group){

          $ref = esc_sql($group['ref']);
          $parent_ref = esc_sql($group['parent_ref']);
          $typeform_ref = esc_sql($group['id']);
          $title = esc_sql($group['title']);
          $doc = esc_sql($group['doc']);
          $sql = <<<EOD
               insert into {$prefix}ts_bb_group
                    (ref, typeform_ref, parent_ref, survey_ref, title, doc, init) 
               values 
                    ('%s', '%s', '%s', '%s', '%s', '%s', now())
EOD;
          $sql = $wpdb->prepare($sql, $ref, $typeform_ref, $parent_ref, $survey_ref, $title, $doc);
          $sql = bb_debug_sql($sql);
          $res = $wpdb->query($sql);
     }

     return $res;
}



function bb_insert_survey($descriptor, $survey, $data){

     $res = false;

     global $wpdb;

     $ref = esc_sql($survey['id']);
     $group_id = esc_sql($descriptor['group_id']);

     $title = bb_trim_incoming_filename($survey['title']);
     $title = esc_sql($title);

     $headline = esc_sql($survey['welcome']);
     $doc = esc_sql(base64_encode($data));

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_survey 
               (ref, group_id, title, headline, doc, init) 
          values 
               ('%s', '%s', '%s', '%s', '%s', now())
EOD;
     $sql = $wpdb->prepare($sql, $ref, $group_id, $title, $headline, $doc);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_parse_survey($doc){

     $res = [];

     $res['id'] = $doc['id'];
     $res['type'] = $doc['type'];
     $res['title'] = $doc['title'];
     $res['welcome'] = $doc['welcome_screens'][0]['title'];

     return $res;
}



function bb_parse_groups($fields, $parent_ref, $res){

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
               $res = bb_parse_groups($childs, $parent_ref, $res);
          }
     }

     return $res;
}



function bb_parse_fields($fields, $parent_ref, $res){

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
               $res = bb_parse_fields($childs, $parent_ref, $res);
          }
     }

     return $res;
}



function bb_parse_yesnos($fields, $parent_ref, $res){

     if(is_null($fields)){ return $res; }
     if(is_null($parent_ref)){ $parent_ref = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($fields as $field){

          $properties = $field['properties'];
          $childs = $field['properties']['fields'];

          if(is_null($childs)){

               switch($field['type']){
                    case 'yes_no':
                         $picks = ['yes', 'no'];
                         foreach($picks as $pick){
                              $yesno = [];
                              $yesno['label'] = $pick;
                              $yesno['id'] = sprintf('%s_%s', $field['ref'], $pick);
                              $yesno['ref'] = sprintf('%s_%s', $field['ref'], $pick);
                              $yesno['parent_ref'] = $parent_ref;
                              $yesno['typeform_ref'] = $field['id'];
                              $yesno['field_ref'] = $field['ref'];
                              $res[]= $yesno;
                         };
                         break;
               }
          }
          else {
               $parent_ref = $field['ref'];
               $res = bb_parse_yesnos($childs, $parent_ref, $res);
          }
     }

     return $res;
}



function bb_parse_choices_of_no_choice($fields, $parent_ref, $res){

     if(is_null($fields)){ return $res; }
     if(is_null($parent_ref)){ $parent_ref = 'root'; }
     if(is_null($res)){ $res = []; }

     foreach($fields as $field){

          $properties = $field['properties'];
          $childs = $field['properties']['fields'];


          if(is_null($childs)){

               switch($field['type']){

                    case 'statement':
                    case 'number':
                    case 'short_text':

                         $choice_of_no_choice = [];
                         $choice_of_no_choice['label'] = 'choice_of_no_choice';
                         $choice_of_no_choice['id'] = sprintf('%s_%s', $field['ref'], 'choice_of_no_choice');
                         $choice_of_no_choice['ref'] = sprintf('%s_%s', $field['ref'], 'choice_of_no_choice');
                         $choice_of_no_choice['parent_ref'] = $parent_ref;
                         $choice_of_no_choice['typeform_ref'] = $field['id'];
                         $choice_of_no_choice['field_ref'] = $field['ref'];

                         $res[]= $choice_of_no_choice;

                         break;
               }
          }
          else {
               $parent_ref = $field['ref'];
               $res = bb_parse_choices_of_no_choice($childs, $parent_ref, $res);
          }
     }

     return $res;
}



function bb_parse_choices($fields, $parent_ref, $res){

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
               $res = bb_parse_choices($childs, $parent_ref, $res);
          }
     }

     return $res;
}



function bb_parse_actions($logics){

     $res = [];

     if(is_null($logics)){ return $res; }
     if(is_null($parent_ref)){ $parent_ref = 'root'; }
     if(is_null($res)){ $res = []; }

     $idx1st = 0;
     foreach($logics as $logic){
          $type = $logic['type'];
          $field_ref = $logic['ref'];
          $idx2nd = 0;
          foreach($logic['actions'] as $action){
               $temp = [];
               $temp['cmd'] = $action['action'];
               if('jump' != $temp['cmd']){
                    continue;
               }
               $ref = sprintf('%s_%s_%s', $field_ref, $idx1st, $idx2nd);
               $temp['ref'] = $ref;
               $temp['type'] = $type;
               $temp['field_ref'] = $field_ref;
               $temp['cmd'] = $action['action'];
               $temp['link_type'] = $action['details']['to']['type'];
               $temp['link_ref'] = $action['details']['to']['value'];
               $temp['condition'] = $action['condition'];
               $res[]= $temp;
               $idx2nd = $idx2nd +1;
          }
          $idx1st = $idx1st +1;
     }

     return $res;
}



function bb_read_typeform_json_descriptors(){

    $path = Path::get_typeform_dir();

     if(!@is_dir($path)){
          $message = esc_html(__('nothing to import', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
          return true;
     }

     $dh = @opendir($path);
     $groups = [];
     while(false !== $file = @readdir($dh)){
          $rsloc = $path.DIRECTORY_SEPARATOR.$file;
          if(is_dir($rsloc)){
               if('..' === $file){
                    continue;
               }
               if('.' === $file){
                    continue;
               }
               $groups[]= ['title'=>$file, 'path'=>$rsloc];
          }
     }

     while(is_resource($dh)){
          @closedir($dh);
     }

     foreach($groups as $group){
          $res = bb_insert_surveygroup($group);
     }

     $files = [];
     foreach($groups as $group){

          $path = $group['path'];
          $group = bb_get_surveygroup_by_path($path)[0];
          if(is_null($group)){
               continue;
          }

          $h = opendir($path);
          if(is_null($h)){ 
               continue;
          }
          while(false !== ($file = readdir($h))){
               if($file != '.' && $file != '..'){
                    preg_match('/(.json$)/', $file, $mtch);
                    if(!empty($mtch)){
                         $files[]= [
                              'path'=>$path.DIRECTORY_SEPARATOR.$file,
                              'title'=>$file,
                              'group_id'=>$group->id
                         ];
                    }
               }
          }

          @closedir($h);
     }

     return $files;
}



function bb_set_target_survey_ref($choice_ref, $target_survey_ref){

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          update {$prefix}ts_bb_choice set target_survey_ref = '%s' where ref = '%s';
EOD;
     $sql = $wpdb->prepare($sql, $target_survey_ref, $choice_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_get_kickoff_field() {

     global $wpdb;
     $prefix = $wpdb->prefix;
     $conf = bb_get_conf()[0];
     if(is_null($conf)){
          return false;
     }
     $title = $conf->root_survey_title;
     if(is_null($title)){
          $title = Proc::KICKOFF_SURVEY_TITLE;
     }
     $sql = <<<EOD
          select f.* from {$prefix}ts_bb_survey s, {$prefix}ts_bb_field f
               where s.ref = f.survey_ref
               and s.title = '%s'
               order by pos
               limit 1;
EOD;
     $sql = $wpdb->prepare($sql, $title);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_set_root_survey_title($root_survey_title){

     $root_survey_title = esc_sql($root_survey_title);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          update {$prefix}ts_bb_conf
               set root_survey_title = '%s'
EOD;
     $sql = $wpdb->prepare($sql, $root_survey_title);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_get_surveygroup_by_path($path){

     $path = esc_sql($path);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_surveygroup 
               where path = '%s' 
EOD;
     $sql = $wpdb->prepare($sql, $path);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);

     return $res;
}



