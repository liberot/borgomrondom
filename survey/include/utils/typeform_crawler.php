<?php defined('ABSPATH') || exit;

function crawl_typeform_survey($survey_file_name){

     $toc = parse_typeform_survey($survey_file_name);

     $proc = [
          'toc'=>$toc,
          'field'=>[
               'ref'=>'root'
          ],
          'mem'=>[],
          'rec'=>[],
          'path'=>[]
     ];

     $proc = walk_typeform_survey($proc);

     return $proc;
}

function walk_typeform_survey($proc){

     if(is_null($proc['field'])){
          return $proc;
     }


// selects answer
     switch($proc['field']['type']){

          case 'short_text':
               $proc['mem']['key'] = '0x01';
               $proc['mem']['val'] = 'Text input';
               break;

          case 'multiple_choice':
               $temp = $proc['field']['choices'][0];
               $proc['mem']['key'] = $temp['ref'];
               $proc['mem']['val'] = $temp['label'];
               break;

          case 'yes_no':
               $proc['mem']['key'] = 'constant';
               $proc['mem']['val'] = '1';
               break;

          case 'picture_choice':
          case 'statement':
          default:
               $proc['mem']['key'] = '0x00';
               $proc['mem']['val'] = 'noticed';
               break;
     }

// stores input
     $proc = store_input($proc);

// stores path record
     $proc = store_path_record($proc);

// evals next node
     $proc = eval_next_node($proc);

// walks
     $proc = walk_typeform_survey($proc);

     return $proc;
}

function store_path_record($proc){

     $proc['field']['answer'] = $proc['mem']['val'];

// todo: determine the possibilities of 'multiple_choice' and 'picture_choice'

     $proc['path'][]= [
          'question'=>$proc['field']['question'],
          'answer'=>$proc['field']['answer'],
          'ref'=>$proc['field']['ref']
     ];

     return $proc;
}

function store_input($proc){

     if(is_null($proc['rec'])){
          $proc['rec'] = [];
     }

     $ref = $proc['field']['ref'];

     $key = $proc['mem']['key'];
     $val = $proc['mem']['val'];

     $proc['rec'][$ref] = [];
     $proc['rec'][$ref][$key] = $val;

     return $proc;
}

function eval_next_default_node($proc){

     $pos = false;
     $idx = 0;
     foreach($proc['toc'] as $field){
          if($field['ref'] == $proc['field']['ref']){
               $pos = $idx;
          }
          $idx = $idx+1;
     }

     if(false === $pos){
          $pos = 0;
     }
     else {
          $pos = $pos+1;
     }

     if($pos >= count($proc['toc'])){
          $proc['field'] = null;
          return $proc;
     }

     if(is_null($proc['toc'][$pos])){
          $proc['field'] = null;
          return $proc;
     }

     $proc['field'] = $proc['toc'][$pos];

     return $proc;
}

function eval_next_node($proc){

// evals next node
     if(is_null($proc['field'])){
          $proc = eval_next_default_node($proc);
          return $proc;
     }

// evals next node
     if(is_null($proc['field']['actions'])){
          $proc = eval_next_default_node($proc);
          return $proc;
     }

// evals conditions of the node
     foreach($proc['field']['actions'] as $action){

          $action_type = $action['action'];
          $condition_operator = $action['condition']['op'];
          $condition_vars = $action['condition']['vars'];

          foreach($condition_vars as $condition){
               switch($condition['type']){
                    case 'choice':
                         $val = $condition['value'];
                         $rec = get_rec_val($proc);
                         if($val == $rec){
                              $condition['res'] = true;
                         }
                         else {
                              $condition['res'] = false;
                         }
                         break;
               }
          }
     }

     $proc = eval_next_default_node($proc);

     return $proc;
}

function get_rec_val($proc){

     $res = null;
     $res = $proc['rec'][$proc['field']['ref']];
     return $res;
}

function parse_typeform_survey($survey_file_name){

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

     $toc = parse_question_groups($doc['fields'], 'root', $doc['logic'], $res=null);

     $toc = flatten_toc_groups($toc, $res=null);

     return $toc;
}

function flatten_toc_groups($toc, $res){

     if(is_null($res)){
          $res = [];
     }

     foreach($toc as $field){
          if(is_null($field['group'])){
               $res[]= $field;
          }
          else {
               foreach($field['group'] as $grouped_field){
                    $res[]= $grouped_field;
               }
          }
     }

     return $res;
}

function eval_actions_of_field($ref, $logic){

     $res = [];

     foreach($logic as $expression){

          if('field' != $expression['type']){
               continue;
          }

          if($ref != $expression['ref']){
               continue;
          }

          $res[]= $expression;
     }

     return $res;
}

function parse_question_groups($procs, $parent, $logic, $res){


     if(is_null($res)){ 
          $res = [];
     }

     if(is_null($parent)){ 
          $parent = 'root'; 
     }

     if(is_null($procs)){ 
          return $res; 
     }

     foreach($procs as $proc){

          $res = parse_tree($res, $parent, $proc, $logic);

          if(!is_null($proc['properties']['fields'])){
               $res = parse_question_groups(
                    $proc['properties']['fields'], 
                    $proc['ref'],
                    $logic,
                    $res
               );

               continue;
          }
     }

     return $res;
}

function parse_tree($tree, $parent, $proc, $logic){

     switch($parent){

          case 'root':

               $tree[]= [ 
                    'ref'=>$proc['ref'],
                    'question'=>$proc['title'],
                    'parent'=>$parent
               ];

               break;

          default:

               $branch = $tree;
               $tree = parse_branch($branch, $parent, $proc, $logic);
               break;
     }

     return $tree;
}

function parse_branch($branch, $parent, $proc, $logic){

     for($idx = 0; $idx < count($branch); $idx++){

          if($parent == $branch[$idx]['ref']){

               $temp = [
                    'ref'=>$proc['ref'],
                    'parent'=>$parent,
                    'question'=>$proc['title'],
                    'type'=>$proc['type']
               ];

               switch($proc['type']){

                    case 'multiple_choice':

                         $temp['choices'] = $proc['properties']['choices'];
                         break;
               }

               $res = eval_actions_of_field($proc['ref'], $logic);
               if(!is_null($res[0]['actions'])){
                    $temp['actions'] = $res[0]['actions'];
               }

               $branch[$idx]['group'][] = $temp; 
          }
          else if(!empty($branch[$idx]['group'])){

               $branch[$idx]['group'] = parse_branch($branch[$idx]['group'], $parent, $proc, $logic);
          }
     }

     return $branch;
}

function flatten_tree($tree, $res=null){

     if(null == $res){
          $res = []; 
     }

     if(null == $tree){ 
          return $res; 
     }

     foreach($tree as $proc){

          if(!empty($proc['group'])){

               $res = flatten_tree($proc['group'], $res);
          }
          else {

               $res[]= $proc;
          }
     }

     return $res;
}
