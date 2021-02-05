<?php defined('ABSPATH') || exit;

function crawl_typeform_survey($survey_file_name){

     $toc = parse_typeform_survey($survey_file_name);

     $node = [
          'toc'=>$toc,
          'field'=>[
               'ref'=>'root'
          ],
          'mem'=>[],
          'rec'=>[],
          'pth'=>[]
     ];

     $node = walk_typeform_survey($node);

     return $node;
}

function walk_typeform_survey($node){

     if(is_null($node['field'])){
          return $node;
     }


// selects answer
     switch($node['field']['type']){

          case 'short_text':
               $node['mem']['key'] = '0x01';
               $node['mem']['val'] = 'Text input';
               break;

          case 'multiple_choice':
               $temp = $node['field']['choices'][0];
               $node['mem']['key'] = $temp['ref'];
               $node['mem']['val'] = $temp['label'];
               break;

          case 'yes_no':
               $node['mem']['key'] = 'constant';
               $node['mem']['val'] = '1';
               break;

          case 'picture_choice':
          case 'statement':
          default:
               $node['mem']['key'] = '0x00';
               $node['mem']['val'] = 'noticed';
               break;
     }

// stores input
     $node = store_input($node);

// stores path record
     $node = store_path_record($node);

// evals next node
     $node = eval_next_node($node);

// walks
     $node = walk_typeform_survey($node);

     return $node;
}

function store_path_record($node){

     $node['field']['answer'] = $node['mem']['val'];

// todo: determine the possibilities of 'multiple_choice' 

     $node['pth'][]= $node['field'];

     return $node;
}

function store_input($node){

     if(is_null($node['rec'])){
          $node['rec'] = [];
     }

     $ref = $node['field']['ref'];

     $key = $node['mem']['key'];
     $val = $node['mem']['val'];

     $node['rec'][$ref] = [];
     $node['rec'][$ref][$key] = $val;

     return $node;
}

function eval_next_default_node($node){

     $pos = false;
     $idx = 0;
     foreach($node['toc'] as $field){
          if($field['ref'] == $node['field']['ref']){
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

     if($pos >= count($node['toc'])){
          $node['field'] = null;
          return $node;
     }

     if(is_null($node['toc'][$pos])){
          $node['field'] = null;
          return $node;
     }

     $node['field'] = $node['toc'][$pos];

     return $node;
}

function eval_next_node($node){

// evals next node
     if(is_null($node['field'])){
          $node = eval_next_default_node($node);
          return $node;
     }

// evals next node
     if(is_null($node['field']['actions'])){
          $node = eval_next_default_node($node);
          return $node;
     }

// evals conditions of the node
     foreach($node['field']['actions'] as $action){

          $action_type = $action['action'];
          $condition_operator = $action['condition']['op'];
          $condition_vars = $action['condition']['vars'];

          foreach($condition_vars as $condition){
               switch($condition['type']){
                    case 'choice':
                         $val = $condition['value'];
                         $rec = get_rec_val($node);
                         break;
               }
          }
     }

     $node = eval_next_default_node($node);

     return $node;
}

function get_rec_val($node){

     $res = null;
     $res = $node['rec'][$node['field']['ref']];
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

function parse_question_groups($nodes, $parent, $logic, $res){


     if(is_null($res)){ 
          $res = [];
     }

     if(is_null($parent)){ 
          $parent = 'root'; 
     }

     if(is_null($nodes)){ 
          return $res; 
     }

     foreach($nodes as $node){

          $res = parse_tree($res, $parent, $node, $logic);

          if(!is_null($node['properties']['fields'])){
               $res = parse_question_groups(
                    $node['properties']['fields'], 
                    $node['ref'],
                    $logic,
                    $res
               );

               continue;
          }
     }

     return $res;
}

function parse_tree($tree, $parent, $node, $logic){

     switch($parent){

          case 'root':

               $tree[]= [ 
                    'ref'=>$node['ref'],
                    'question'=>$node['title'],
                    'parent'=>$parent
               ];

               break;

          default:

               $branch = $tree;
               $tree = parse_branch($branch, $parent, $node, $logic);
               break;
     }

     return $tree;
}

function parse_branch($branch, $parent, $node, $logic){

     for($idx = 0; $idx < count($branch); $idx++){

          if($parent == $branch[$idx]['ref']){

               $temp = [
                    'ref'=>$node['ref'],
                    'parent'=>$parent,
                    'question'=>$node['title'],
                    'type'=>$node['type']
               ];

               switch($node['type']){

                    case 'multiple_choice':

                         $temp['choices'] = $node['properties']['choices'];
                         break;
               }

               $res = eval_actions_of_field($node['ref'], $logic);
               if(!is_null($res[0]['actions'])){
                    $temp['actions'] = $res[0]['actions'];
               }

               $branch[$idx]['group'][] = $temp; 
          }
          else if(!empty($branch[$idx]['group'])){

               $branch[$idx]['group'] = parse_branch($branch[$idx]['group'], $parent, $node, $logic);
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

     foreach($tree as $node){

          if(!empty($node['group'])){

               $res = flatten_tree($node['group'], $res);
          }
          else {

               $res[]= $node;
          }
     }

     return $res;
}
