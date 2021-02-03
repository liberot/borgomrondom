<?php defined('ABSPATH') || exit;

function crawl_typeform_survey($survey_file_name){
     $toc = parse_typeform_survey($survey_file_name);
     $res = walk_through_survey($toc, $depth=15, $toc[0], null);
     return $res;
}

function walk_through_survey($toc, $target_level, $field, $res){

     if(is_null($res)){
          $res = [];
     }

     $default_field_idx = 0;
     for($level = 0; $level < $target_level; $level++){

          $res = add_field_to_result($field, $res);

          if(is_null($field['choices'])){
              $answer = 'noticed';
              $rec = add_answer_rec($field, $rec, '0x00', $answer);
              if(++$default_field_idx < count($toc)){
                   $field = $toc[$default_field_idx];
                   $field['answer'] = $answer;
              }
          }
          else {
              foreach($field['choices'] as $choice){
                   $rec = add_answer_rec($field, $rec, $choice['ref'], $choice['label']);

                   $field = eval_next_field($toc, $res, $field);
                   $field['answer'] = $choice['label'];

                   $res = add_field_to_result($field, $res);
              }
          }
     }

     return $res;
}

function add_answer_rec($field, $rec, $ref, $val){

     if(is_null($rec)){
          $rec = [];
     }

     $rec[$field['ref']] = [];
     $rec[$field['ref']][$ref] = $val;

     return $rec;
}

function eval_next_field($toc, $rec, $field){

     $res = null;

// mock
// todo: eval the next field from the input
     $pos = array_search($field, $toc);
     $pos++;
     if($pos < count($toc)){
          $res = $toc[$pos];
     }

     return $res;
}

function add_field_to_result($field, $res){

     $buf = '';
     $buf.= PHP_EOL;
     $buf.= sprintf('--------------------------------------------------------');
     $buf.= PHP_EOL;
     $buf.= sprintf('////////////////////////////////////////////////////////');
     $buf.= PHP_EOL;
     $buf.= sprintf('group:     %s', $field['parent']);
     $buf.= PHP_EOL;
     $buf.= sprintf('ref:       %s', $field['ref']);
     $buf.= PHP_EOL;
     $buf.= sprintf('question:  %s', $field['question']);
     $buf.= PHP_EOL;
     $buf.= sprintf('answer:    %s', $field['answer']);
     $buf.= PHP_EOL;
     $buf.= sprintf('--------------------------------------------------------');
     $buf.= PHP_EOL;
     $buf.= PHP_EOL;

     if(false === array_search($buf, $res)){
          array_push($res, $buf);
     }

     return $res;
}

/*
function crawl($toc, $branch, $field){

     if(is_null($branch)){
          $branch = [];
     }

     if(is_null($branch['knots'])){
          $branch['knots'] = []; 
     }

     if(is_null($field)){
          $field = $toc[0];
     }

     $sprout = [];
     $sprout['ref'] = $field['ref'];
     $sprout['question'] = preg_replace('/(\r\n)+|\r+|\n+|\t+/', ' ', $field['question']);
     $sprout['answer'] = 'noticed';

     $branch['branch']= $sprout;

     $field = eval_next_field($toc, $rec, $field);
     if(!is_null($field)){
          $branch['knots'][]= crawl($toc, $branch, $field);
     }

     return $branch;
}

*/

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
