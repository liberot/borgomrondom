<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_books', 'exec_get_books');
function exec_get_books(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_get_books', []);

     $coll = get_books();

     $message = esc_html(__('books loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_book_by_id', 'exec_get_book_by_id');
function exec_get_book_by_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $book_id = trim_incoming_numeric($_POST['book_id']);

     init_log('admin_post_exec_get_book_by_id', ['book_id'=>$book_id]);

     $coll = [];
     $coll['book'] = get_book_by_id($book_id);
     $coll['chapter'] = get_chapter_by_book_id($book_id);

     $message = esc_html(__('the book is loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_exec_get_chapter_by_book_id', 'exec_get_chapter_by_book_by_id');
function exec_get_chapter_by_book_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $book_id = trim_incoming_numeric($_POST['book_id']);
     $coll = get_chapter_by_book_id($book_id);

     init_log('admin_exec_get_chapter_by_book_id', ['book_id'=>$book_id]);

     $message = esc_html(__('the chapters is loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_exec_get_spreads_by_chapter_id', 'exec_get_spreads_by_chapter_by_id');
function exec_get_spreads_by_chapter_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $chapter_id = trim_incoming_numeric($_POST['chapter_id']);

     init_log('admin_exec_get_spreads_by_chapter_id', ['chapter_id'=>$chapter_id]);

     $coll = get_spreads_by_chapter_id($chapter_id);

     $message = esc_html(__('the spreads is loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$spreads, 'coll'=>$coll));

     return true;
}

/***
     sets up a book record
     and chapters
     and spreads within
 */
add_action('admin_post_exec_init_book_by_thread_id', 'exec_init_book_by_thread_id');
function exec_init_book_by_thread_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $author_id = get_author_id();

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $section_id = 0;

     init_log('admin_post_exec_init_book_by_thread_id', ['thread_id'=>$thread_id]);

     $thread = get_thread_by_id($thread_id)[0];
     if(is_null($thread)){
          $message = esc_html(__('no such thread', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $thread->post_content = pagpick($thread->post_content);

     $spread_refs = [];

// ---------------------------- book
     $book_id = setup_new_book('Title of a Book');

// ---------------------------- chapter ZERO
// ---------------------------- descriptional chapter
     $chapter_id = add_chapter($thread_id, $section_id, $book_id);

// ---------------------------- cover
     $spread_ids[]= add_cover($thread_id, $section_id, $book_id, $chapter_id);
     $spread_refs[]= 'cover';

// ---------------------------- inside cover
     $spread_ids[]= add_inside_cover($thread_id, $section_id, $book_id, $chapter_id);
     $spread_refs[]= 'inside_cover';

// ---------------------------- intro 
     $spread_ids[]= add_intro($thread_id, $section_id, $book_id, $chapter_id);
     $spread_refs[]= 'intro';

// ---------------------------- spreads 
     $temp_section_id = null;
     $temp_group_ref = null;

     foreach($thread->post_content['book'] as $toc_entry){

          $thread_id = $toc_entry['threadId'];
          $section_id = $toc_entry['sectionId'];
          $group_ref = $toc_entry['groupRef'];
          $panel_ref = $toc_entry['panelRef'];

          if(is_null($section_id)){ 
               continue; 
          }

// ---------------------------- chapter
          $chapter_id = add_chapter($thread_id, $section_id, $book_id);


// ---------------------------- spread
          $spread_refs[]= add_spread($thread_id, $section_id, $panel_ref, $book_id, $chapter_id);

     }

// ---------------------------- toc
     $toc = [];

     $toc['book'] = $thread->post_content['book'];
     $toc['post_excerpt'] = $thread->post_excerpt;
     $toc['spread_refs'] = $spread_refs;

     $toc_id = add_toc($thread_id, $section_id, $book_id, $toc);

     $coll['book'] = get_book_by_id($book_id);
     $coll['toc'] = get_toc_by_book_id($book_id);
     $coll['chapter'] = get_chapter_by_book_id($book_id);

     if(!is_null($coll['chapter'])){
          foreach($coll['chapter'] as $chapter){
               $chapter->spreads = get_spreads_by_chapter_id($chapter->ID);
          }
     }

     $message = esc_html(__('book is inited', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


