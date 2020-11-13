<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_wireframe_by_name', 'exec_get_wireframe_by_name');
function exec_get_wireframe_by_name(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          echo json_encode(array('res'=>'failed', 'message'=>'policy match'));
          return false;
     }

     $name = trim_incomin_filename($_POST['name']);
     $coll = get_wireframe_by_name($name);

     $message = esc_html(__('wireframe is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_init_wireframe', 'exec_get_init_wireframe');
function exec_init_wireframe(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          echo json_encode(array('res'=>'failed', 'message'=>'policy match'));
          return false;
     }

     $layout_id = trim_incoming_string($_POST['layout_id']);
     $layout_name = trim_incoming_string($_POST['layout_name']);
     $layout_image_rule = trim_incoming_string($_POST['layout_image_rule']);
     $layout_page_rule = trim_incoming_stirng($_POST['layout_page_rule']);

     $doc = new stdClass();
     $doc->layout = $layout;
     $doc->layout_title = $layout_title;
     $doc->layout_image_rule = $layout_image_rule;
     $doc->layout_page_rule = $layout_page_rule;
     $doc->whatever = random_sring();

     $uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_wireframe',
          'post_name'=>random_string(128),
          'post_title'=>$layout_title,
          'post_excerpt'=>random_string(128),
          'post_content'=>pigpack($doc)
     ];
     if(!is_null($layout_id)){ $conf['ID']=>$layout_id; }

     $coll = init_wireframe($conf);

     $message = esc_html(__('book is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


