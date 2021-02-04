<?php defined('ABSPATH') || exit;

function crawl_typeform_survey($survey_file_name){

     $toc = parse_typeform_survey($survey_file_name);
     $conf = [
          'toc'=>$toc,
          'node'=>[
               'ref'=>'root'
          ],
          'mem'=>[],
          'rec'=>[]
     ];

     $conf = walk_typeform_survey($conf);

     return $conf;
}

function walk_typeform_survey($conf){

     if(is_null($conf['node'])){
          return $conf;
     }

// todo: eval input
     $conf['mem']['key'] = '0x00';
     $conf['mem']['val'] = 'default';

// stores input
     $conf = store_input($conf);

// evals next node
     $conf = eval_next_node($conf);

// walks
     $conf = walk_typeform_survey($conf);

     return $conf;
}

function store_input($conf){

     if(is_null($conf['rec'])){
          $conf['rec'] = [];
     }

     $ref = $conf['node']['ref'];

     $key = $conf['mem']['key'];
     $val = $conf['mem']['val'];

     $conf['rec'][$ref] = [];
     $conf['rec'][$ref][$key] = $val;

     return $conf;
}

function eval_next_default_node($conf){

     $pos = false;
     $idx = 0;
     foreach($conf['toc'] as $field){
          if($field['ref'] == $conf['node']['ref']){
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

     if($pos >= count($conf['toc'])){
          $conf['node'] = null;
          return $conf;
     }

     if(is_null($conf['toc'][$pos])){
          $conf['node'] = null;
          return $conf;
     }

     $conf['node'] = $conf['toc'][$pos];

     return $conf;
}

function eval_next_node($conf){

// evals next default node
     if(is_null($conf['node'])){
          $conf = eval_next_default_node($conf);
          return $conf;
     }

// evals next default node
     if(is_null($conf['node']['actions'])){
          $conf = eval_next_default_node($conf);
          return $conf;
     }

// evals conditions of the node
     foreach($conf['node']['actions'] as $action){

          $log = $action['condition']['op'];
          $conditions = $action['condition']['vars'];

          foreach($conditions as $condition){

               switch($condition['type']){

                    case 'field':
// not be evald
                         break;

                    case 'choice':
                         $val = $condition['value'];
                         $rec = get_rec_val($conf);
print '>rec:';
print PHP_EOL;
print_r($val);
print PHP_EOL;
print_r($rec);
print PHP_EOL;
                         break;
               }
          }
     }

     $conf = eval_next_default_node($conf);

     return $conf;
}

function get_rec_val($conf){

     $res = null;
     $res = $conf['rec'][$conf['node']['ref']];
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

     $lgc = $doc['logic'];

     $toc = parse_question_groups($doc['fields'], null, null, $lgc);
     $toc = flatten_toc_groups($toc, null);

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
// todo rec
               foreach($field['group'] as $grouped_field){
                    $res[]= $grouped_field;
               }
          }
     }

     return $res;
}

function eval_actions_of_field($ref, $lgc){

     $res = [];

     foreach($lgc as $logic){

          if('field' != $logic['type']){
               continue;
          }

          if($ref != $logic['ref']){
               continue;
          }

          $res[]= $logic;
     }

     return $res;
}

function parse_question_groups($nodes, $parent=null, $res=null, $lgc){


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

          $res = parse_tree($res, $parent, $node, $lgc);

          if(!is_null($node['properties']['fields'])){
               $res = parse_question_groups(
                    $node['properties']['fields'], 
                    $node['ref'],
                    $res,
                    $lgc
               );

               continue;
          }
     }

     return $res;
}

function parse_tree($tree, $parent, $node, $lgc){

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
               $tree = parse_branch($branch, $parent, $node, $lgc);
               break;
     }

     return $tree;
}

function parse_branch($branch, $parent, $node, $lgc){

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

               $res = eval_actions_of_field($node['ref'], $lgc);
               if(!is_null($res[0]['actions'])){
                    $temp['actions'] = $res[0]['actions'];
               }

               $branch[$idx]['group'][] = $temp; 
          }
          else if(!empty($branch[$idx]['group'])){

               $branch[$idx]['group'] = parse_branch($branch[$idx]['group'], $parent, $node, $lgc);
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
