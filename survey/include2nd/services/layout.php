<?php defined('ABSPATH') || exit;



add_action('admin_post_bb_get_layoutgroups', 'bb_exec_get_layoutgroups');
function bb_exec_get_layoutgroups(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = bb_get_layoutgroups();

     $message = esc_html(__('layoutgroups loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}



add_action('admin_post_bb_get_layouts_by_group_and_code', 'bb_exec_get_layouts_by_group_and_code');
function bb_exec_get_layouts_by_group_and_code(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $group_id = bb_trim_incoming_filename($_POST['group_id']);
     $code = bb_trim_incoming_filename($_POST['code']);

     $coll = bb_get_layouts_by_group_and_code($group_id, $code);
     // $coll = bb_get_layouts_by_code($code);

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
add_action('admin_post_bb_import_layouts', 'bb_exec_import_layouts');
function bb_exec_import_layouts(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = bb_import_layouts();

     $message = esc_html(__('did import the layouts', 'bookbuilder'));

     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


