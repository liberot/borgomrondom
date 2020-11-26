<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_init_asset_by_panel_ref', 'exec_init_asset_by_panel_ref');
function exec_init_asset_by_panel_ref(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $section_id = trim_incoming_filename($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     $panel_ref = get_session_ticket('panel_ref');

     $image = $_POST['base'];
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

     $layout_code = 'P';
     $layout_code = trim_incoming_filename($_POST['layout_code']);

     if(Proc::EVAL_UPLOADED_ASSET_SIZE){
          $image = add_base_to_chunk($image);
          $size = getimagesize('data://'.$image);
          if(null != $size){
               if(intval($size[0]) >= intval($size[1])){
                    $layout_code = 'L';
               }
          }
          $image = remove_base_from_chunk($image);
     }

     $indx = trim_incoming_filename($_POST['indx']);


     switch(Proc::MEDIA_UPLOAD_PROC){

          case Proc::BASE64_UPLOAD:
               $conf = [
                    'post_type'=>'surveyprint_asset',
                    'post_author'=>get_author_id(),
                    'post_title'=>$indx,
                    'post_excerpt'=>$panel_ref,
                    'post_name'=>$layout_code,
                    'post_parent'=>$section_id,
                    'post_content'=>$image
               ];
               if(Proc::UPDATE_ON_PERSIST){
                    $asset = get_assets_by_panel_ref($section_id, $panel_ref, 1)[0];
                    if(!is_null($asset)){
                         $conf['ID'] = $asset->ID;
                    }
               }
               $res = init_asset($conf);
               break;

          case Proc::FILE_UPLOAD:

               $upload_directory = Path::get_upload_path();
               @mkdir($upload_directory);
               $upload_directory = sprintf('%s/%s', $upload_directory, get_author_id());
               @mkdir($upload_directory);
               $upload_directory = sprintf('%s/%s', $upload_directory, $thread_id);
               @mkdir($upload_directory);
               $asset_name = sprintf('%s.png', random_string(32));
               $upload_path = sprintf('%s/%s', $upload_directory, $asset_name);
               $image = base64_decode($image);
               file_put_contents($upload_path, $image);

               // @chmod($upload_directory, 0644);

               $attachment = array(
                    'post_author'=>get_author_id(),
                    'post_title'=>$asset_name,
                    'post_name'=>$asset_name,
                    'post_parent'=>$section_id,
                    'post_excerpt'=>$panel_ref,
                    'post_mime_type'=>'image/png',
                    'post_content'=>psuuid()
               );
               $attach_id = wp_insert_attachment($attachment, $upload_path, $section_id);
               break;
     }

     $max = 1;
     $coll['assets'] = get_assets_by_panel_ref($section_id, $panel_ref, $max);

     $message = esc_html(__('file is uploaded', 'nosuch'));
     echo json_encode(['res'=>'success', 'message'=>$message, 'coll'=>$coll]);
};

add_action('admin_post_exec_get_assets_by_panel_ref', 'exec_get_assets_by_panel_ref');
function exec_get_assets_by_panel_ref(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
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

