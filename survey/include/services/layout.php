<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_init_layout', 'exec_init_layout');
function exec_init_layout(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_init_layout', []);

     $rule = trim_incoming_filename($_POST['rule']);
     $group = trim_incoming_filename($_POST['group']);

     $doc = walk_the_doc($_POST['doc']);
     $doc = pigpack($doc);
     if(false == $doc){
          $message = esc_html(__('doc invalid', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $conf = [
          'post_type'=>'surveyprint_layout',
          'post_name'=>'layout_rule',
          'post_title'=>$group,
          'post_excerpt'=>$rule,
          'post_content'=>$doc,
          'tags_input'=>$tags_input
     ];

     $coll = init_layout($conf);
     $message = esc_html(__('layout inited', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll, 'term_id'=>$term_id));
}

add_action('admin_post_exec_get_layouts_by_group', 'exec_get_layouts_by_group');
function exec_get_layouts_by_group(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_get_layouts_by_group', []);

     $group = trim_incoming_filename($_POST['group']);
     $coll = get_layouts_by_group($group);
     $message = esc_html(__('layouts loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_layout_by_group_and_rule', 'exec_get_layout_by_group_and_rule');
function exec_get_layout_by_group_and_rule(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_get_layout_by_group_and_rule', []);

     $group = trim_incoming_filename($_POST['group']);
     $rule = trim_incoming_filename($_POST['rule']);
     $coll = get_layout_by_group_and_rule($group, $rule);
     $message = esc_html(__('layouts loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


/***********************************************************************
 svg layout import
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 -----------------------------------------------------------------------
 --
 does work with svg plain documents
 as i copy and paste the targets into place from some wild svg documents
 into svg documents of 1.1 type without sperenzchen
 as for to find the grey slots for to place the image assets
 and the hearties that is rendered above the masked image assets
 -----------------------------------------------------------------------
*/
add_action('admin_post_exec_import_layouts', 'exec_import_layouts');
function exec_import_layouts(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('exec_import_layouts', []);

     $coll = import_layouts();

     $message = esc_html(__('did import the layouts', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


