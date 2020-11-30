<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_books', 'exec_get_books');
function exec_get_books(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = get_books();

     $message = esc_html(__('books loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_book_by_id', 'exec_get_book_by_id');
function exec_get_book_by_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $book_id = trim_incoming_numeric($_POST['book_id']);
     $coll = [];
     $coll['book'] = get_book_by_id($book_id);
     $coll['chapter'] = get_chapter_by_book_id($book_id);

     $message = esc_html(__('the book is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_exec_get_chapter_by_book_id', 'exec_get_chapter_by_book_by_id');
function exec_get_chapter_by_book_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $book_id = trim_incoming_numeric($_POST['book_id']);
     $coll = get_chapters_by_book_id($book_id);

     $message = esc_html(__('the chapters is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_exec_get_spreads_by_chapter_id', 'exec_get_spreads_by_chapter_by_id');
function exec_get_spreads_by_chapter_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $chapter_id = trim_incoming_numeric($_POST['chapter_id']);
     $coll = get_spreads_by_chapter_id($chapter_id);

     $message = esc_html(__('the spreads is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$spreads, 'coll'=>$coll));

     return true;
}

add_action('admin_post_exec_init_book_by_thread_id', 'exec_init_book_by_thread_id');
function exec_init_book_by_thread_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $author_id = get_author_id();

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $thread = get_thread_by_id($thread_id)[0];
     if(is_null($thread)){
          $message = esc_html(__('no such thread', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $thread->post_content = pagpick($thread->post_content);

// todo:: different sections of a thread
     $section_id = trim_incoming_filename($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

// todo: fixdiss
     $spread_ids = [];
     $spread_refs = [];

// ---------------------------- book
     $book_id = setup_new_book('Title of a Book');

// ---------------------------- chapter
     $chapter_id = add_chapter($book_id, 'Title of a Chapter', 'Description');

// ---------------------------- spreads
// ---------------------------- cover
     $spread_ids[]= add_cover($chapter_id, 'Title of the Cover');
     $spread_refs[]= 'cover';


// ---------------------------- inside cover
     $spread_ids[]= add_inside_cover($chapter_id, 'Title of the Inside Cover');
     $spread_refs[]= 'inside_cover';

// ---------------------------- intro 
     $spread_ids[]= add_intro($chapter_id, 'Title of the Intro');
     $spread_refs[]= 'intro';


// ---------------------------- chapter
     $chapter_id = add_chapter($book_id, 'Title of a Chapter', 'Description');


// ---------------------------- pages
// print_r($thread); exit();
     foreach($thread->post_content['book'] as $ref){
          $res = add_spread($section_id, 'Title of a Spread', $chapter_id, $ref);
          if(false != $res){
               $spread_ids[]= $res['spread_id'];
               $spread_refs[]= $res['spread_ref'];
          }
     }

// ---------------------------- toc
     $toc = [];
     $toc['book'] = $thread->post_content['book'];
     $toc['spread_ids'] = $spread_ids;
     $toc['spread_refs'] = $spread_refs;
     $toc['post_excerpt'] = $thread->post_excerpt;

     $toc_id = add_toc($book_id, 'Title of a ToC', $toc);

     $coll['book'] = get_book_by_id($book_id);
     $coll['toc'] = get_toc_by_book_id($book_id);
     $coll['chapter'] = get_chapter_by_book_id($book_id);

     if(!is_null($coll['chapter'])){
          foreach($coll['chapter'] as $chapter){
               $chapter->spreads = get_spreads_by_chapter_id($chapter->ID);
          }
     }

     $message = esc_html(__('book is inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


