<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_init_asset_by_panel_ref', 'exec_init_asset_by_panel_ref');
function exec_init_asset_by_panel_ref(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $section_id = trim_incoming_filename($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

     $indx = trim_incoming_filename($_POST['indx']);

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);

     $layout_code = trim_incoming_filename($_POST['layout_code']);

     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     if(is_null($panel)){
          $message = esc_html(__('no panel', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $image = $_POST['image'];
     $image = remove_base_from_chunk($image);

     $temp = base64_decode($image, true);
     if(false == $temp){
          $message = esc_html(__('corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'image'=>$image));
          return false;
     }

     $finf = new finfo(FILEINFO_MIME);
     $temp = $finf->buffer($temp);
     if(-1 == strpos('image/png', $temp)){
          $message = esc_html(__('corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'image'=>$image));
          return false;
     }

     $finf = new finfo(FILEINFO_DEVICES);
     $temp = $finf->buffer($temp);
     if(-1 == strpos('ASCII text', $temp)){
          $message = esc_html(__('corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'image'=>$image));
          return false;
     }

     $conf = [
          'post_type'=>'surveyprint_asset',
          'post_author'=>get_author_id(),
          'post_title'=>$indx,
          'post_excerpt'=>$panel_ref,
          'post_name'=>$layout_code,
          'post_parent'=>$section_id,
          'post_content'=>$image
     ];

     $res = init_asset($conf);

     $max = 1;
     $coll['assets'] = get_assets_by_panel_ref($section_id, $panel_ref, $max);

     $message = esc_html(__('file is uploaded', 'nosuch'));
     echo json_encode(['res'=>'success', 'message'=>$message, 'coll'=>$coll]);
};

add_action('admin_post_exec_get_assets_by_panel_ref', 'exec_get_assets_by_panel_ref');
function exec_get_assets_by_panel_ref(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $section_id = trim_incoming_filename($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);

     $max = 1;
     $res = get_assets_by_panel_ref($section_id, $panel_ref, $max);
     $message = esc_html(__('assets loaded', 'nosuch'));
     echo json_encode(['res'=>'success', 'message'=>$message, 'coll'=>$res, 'panel_id'=>$panel_id]);
};

