<?php defined('ABSPATH') || exit;

function crawl_typeform_survey($survey_file_name){

     $toc = parse_typeform_survey($survey_file_name);
     $conf = [
          'rec',
          'toc'=>$toc,
          'node'
     ];

     $conf = walk_typeform_survey($conf);
print_r($conf['node']);
print PHP_EOL;

     $conf = walk_typeform_survey($conf);
print_r($conf['node']);
print PHP_EOL;

     $conf = walk_typeform_survey($conf);
print_r($conf['node']);
print PHP_EOL;

exit();

     return $conf;
}

function walk_typeform_survey($conf){

     $res = null;

// todo: store input
     $conf['key'] = '0x00';
     $conf['val'] = 'default';
     $res = store_input($conf);

// loads next field
     $res = eval_next_node($conf);

     return $res;
}

function store_input($conf){

     if(is_null($conf['rec'])){
          $conf['rec'] = [];
     }

     $key = $conf['key'];
     $val = $conf['val'];
     $ref = $conf['node']['ref'];

     $conf['rec'][$ref] = [];
     $conf['rec'][$ref][$key] = $val;

     return $conf;
}

function eval_next_default_node($conf){

     $res = null;

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
          return $res;
     }

     if(is_null($conf['toc'][$pos])){
          return $res;
     }

     $res = $conf;

     $res['node'] = $conf['toc'][$pos];

     return $res;
}

function eval_next_node($conf){

     $res = null;

     if(is_null($conf['node'])){
          $res = eval_next_default_node($conf);
          return $res;
     }

     if(is_null($conf['node']['actions'])){
          $res = eval_next_default_node($conf);
          return $res;
     }

// todo: evaluate: conditions of the node
// mock: default next node
     $res = eval_next_default_node($conf);

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
