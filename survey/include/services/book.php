<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_books', 'exec_get_books');
function exec_get_books(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
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

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
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

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
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

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
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

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $author_id = get_author_id();

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $section_id = trim_incoming_filename($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

     $thread_toc = get_toc_by_thread_id($thread_id)[0];
     if(is_null($thread_toc)){
          $message = esc_html(__('no toc', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $thread_toc->post_content = pagpick($thread_toc->post_content);

     if(null == $thread_toc->post_content['booktoc']){
          $message = esc_html(__('no toc records', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

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
     foreach($thread_toc->post_content['booktoc'] as $panel){
          $res = add_spread($section_id, 'Title of a Spread', $chapter_id, $panel);
          if(false != $res){
               $spread_ids[]= $res['spread_id'];
               $spread_refs[]= $res['spread_ref'];
          }
     }

// ---------------------------- toc  
     $toc_id = add_toc($book_id, 'Title of a ToC', $thread_toc, $spread_ids, $spread_refs);

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

function setup_new_book($title){

     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());
     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');
     $uuid = psuuid();

     $conf = [
          'post_type'=>'surveyprint_book',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$thread_id,
          'post_name'=>$uuid,
          'post_excerpt'=>$uuid,
          'post_content'=>random_string(131)
     ];
     $book_id = init_book($conf);
     return $book_id;
}

function add_chapter($book_id, $title, $desc){

     $title = esc_sql(trim_for_print($title));
     $desc = esc_sql(trim_for_print($desc));
     $author_id = esc_sql(get_author_id());
     $book_id = esc_sql($book_id);
     $uuid = psuuid();

     $uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_chapter',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$book_id,
          'post_name'=>$uuid,
          'post_excerpt'=>$uuid,
          'post_content'=>$desc
     ];

     $chapter_id = init_chapter($conf);
     return $chapter_id;
}

function add_cover($chapter_id, $title){

     $chapter_id = esc_sql($chapter_id);
     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());
     $uuid = psuuid();

     $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-cover.json';
     $__doc = @file_get_contents($path);
     $__doc = json_decode($__doc);
     $doc = clone $__doc;

     $doc->uuid = $uuid;
     $doc->panelId = 'cover'; 

     $conf = new stdClass();
     $conf->max_assets = '0';
     $conf->layout_group = 'cover';
     $doc->conf = $conf;

     $conf = [
          'post_type'=>'surveyprint_spread',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$chapter_id,
          'post_name'=>$uuid,
          'post_excerpt'=>'cover',
          'post_content'=>pigpack($doc)
     ];
     $spread_id = init_spread($conf);
     return $spread_id;
}

function add_inside_cover($chapter_id, $title){

     $chapter_id = esc_sql($chapter_id);
     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());
     $uuid = psuuid();

     $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-cover-inside.json';
     $__doc = @file_get_contents($path);
     $__doc = json_decode($__doc);
     $doc = clone $__doc;
     $uuid = psuuid();
     $doc->uuid = $uuid;

     $doc->conf = [];
     $doc->conf['max_assets'] = '0';
     $doc->conf['layout_group'] = 'inside_cover';
     $doc->conf['layout_code'] = '';

     $conf = [
          'post_type'=>'surveyprint_spread',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$chapter_id,
          'post_name'=>$uuid,
          'post_excerpt'=>'inside_cover',
          'post_content'=>pigpack($doc)
     ];
     $spread_id = init_spread($conf);
     return $spread_id;
}

function add_intro($chapter_id, $title){

     $chapter_id = esc_sql($chapter_id);
     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());
     $uuid = psuuid();

     $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-intro.json';
     $__doc = @file_get_contents($path);
     $__doc = json_decode($__doc);
     $doc = clone $__doc;
     $uuid = psuuid();
     $doc->uuid = $uuid;
     $doc->panelId = 'intro_page';

     $conf = new stdClass();
     $conf->max_assets = '0';
     $conf->layout_group = 'intro_page';
     $conf->layout_code = '';
     $doc->conf = $conf;

     $conf = [
          'post_type'=>'surveyprint_spread',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$chapter_id,
          'post_name'=>$uuid,
          'post_excerpt'=>'intro',
          'post_content'=>pigpack($doc)
     ];
     $spread_id = init_spread($conf);
     return $spread_id;
}

function add_toc($book_id, $title, $toc, $spread_ids, $spread_refs){

     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());
     $book_id = esc_sql($book_id);
     $uuid = psuuid();

     $toc->post_content['spread_ids'] = $spread_ids;
     $toc->post_content['spread_refs'] = $spread_refs;
     $uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$book_id,
          'post_name'=>$uuid,
          'post_excerpt'=>$toc->post_excerpt,
          'post_content'=>pigpack($toc->post_content)
     ];
     $toc_id = init_toc($conf);

     return $toc_id;
}

function add_spread($section_id, $title, $chapter_id, $panel_ref){

     $panel = get_panel_by_ref($section_id, $panel_ref)[0];

     $author_id = get_author_id();

     if(null == $panel){ return false; }

     $panel->post_content = pagpick($panel->post_content);

     $layout_code = $panel->post_content['conf']['layout_code'];
     $layout_group = $panel->post_content['conf']['layout_group'];

     $layout_group = is_null($layout_group) ? 'default' : $layout_group;

$layout_code = 'P';

     $layout_docs = get_layout_presets_by_group_and_rule($layout_group, $layout_code);
     $__doc = $layout_docs[0];

     if(null == $__doc){
          $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-spread.json';
          $__doc = @file_get_contents($path);
          $__doc = json_decode($__doc);
          $__doc = walk_the_doc($__doc);
     }
     else {
          $__doc = $layout_docs[0]->post_content;
          $__doc = pagpick($__doc);
     }

     if(null == $__doc){ return false; }

     $doc = $__doc;

     $uuid = psuuid();
     $doc['uuid'] = $uuid;
     $doc['assets'][0]['text'] = [trim_for_print($panel->post_content['answer'])];
     $doc['assets'][1]['text'] = [];

     $max = 1;
     $indx = 0;
     $asis = [];
     foreach($doc['assets'] as $asset){
          if('image' != $asset['type']){ 
               $asis[]= $asset;
               continue; 
          }
          $asset['src'] = '';
          $asset['locator'] = '';
          $asset['conf']['ow'] = '';
          $asset['conf']['oh'] = '';
          $uploaded_asset = get_assets_by_panel_ref($section_id, $panel->post_excerpt, $max)[0];
          if(null != $uploaded_asset){
               $asset['src'] = add_base_to_chunk($uploaded_asset->post_content);
               $asset = fit_image_asset_to_slot($doc, $asset);
          }
          $asis[]= $asset;
     }
     $doc['assets'] = $asis;

     $doc['panelId'] = $panel->ID;
     $doc['conf'] = $panel->post_content['conf'];
     $ref = $panel->post_excerpt;
     $uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_spread',
          'post_author'=>$author_id,
          'post_title'=>$title,
          'post_parent'=>$chapter_id,
          'post_name'=>$uuid,
          'post_excerpt'=>$ref,
          'post_content'=>pigpack($doc)
     ];
     $res = [];
     $res['spread_id'] = init_spread($conf);
     $res['spread_ref'] = $ref;
     return $res;
}

