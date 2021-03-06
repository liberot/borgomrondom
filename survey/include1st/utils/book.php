<?php defined('ABSPATH') || exit;

add_action('init', 'init_book_utils');
function init_book_utils(){

     $res = register_post_type(
          'surveyprint_book',  [
               'label'                  =>'SurveyPrint Book',
               'description'            =>'SurveyPrint Book',
               'public'                 => false,
               'hierarchical'           => true,
               'exclude_from_search'    => true,
               'publicly_queryable'     => false,
               'show_ui'                => false,
               'show_in_menu'           => false,
               'show_in_nav_menus'      => false,
               'query_var'              => true,
               'rewrite'                => false,
               'capability_type'        => 'post',
               'has_archive'            => true,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );

     $res = register_post_type(
          'surveyprint_chapter',  [
               'label'                  =>'SurveyPrint Chapter',
               'description'            =>'SurveyPrint Chapter',
               'public'                 => false,
               'hierarchical'           => true,
               'exclude_from_search'    => true,
               'publicly_queryable'     => false,
               'show_ui'                => false,
               'show_in_menu'           => false,
               'show_in_nav_menus'      => false,
               'query_var'              => true,
               'rewrite'                => false,
               'capability_type'        => 'post',
               'has_archive'            => true,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );

     $res = register_post_type(
          'surveyprint_spread',  [
               'label'                  =>'SurveyPrint Spread',
               'description'            =>'SurveyPrint Spread',
               'public'                 => false,
               'hierarchical'           => true,
               'exclude_from_search'    => true,
               'publicly_queryable'     => false,
               'show_ui'                => false,
               'show_in_menu'           => false,
               'show_in_nav_menus'      => false,
               'query_var'              => true,
               'rewrite'                => false,
               'capability_type'        => 'post',
               'has_archive'            => true,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );

     return $res;
}

function init_book($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function init_chapter($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function init_spread($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_books(){
     $res = query_posts([
          'post_type'=>'surveyprint_book'
     ]);
     return $res;
}

function get_book_by_id($book_id){

     $book_id = esc_sql($book_id);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;
$sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_book' and post_author = '{$author_id}' and ID = '{$book_id}';
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_book',
          'post_author'=>$author_id,
          'ID'=>$book_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_chapter_by_book_id($book_id){

     $book_id = esc_sql($book_id);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts 
               where post_type = 'surveyprint_chapter' 
               and post_author = '{$author_id}' 
               and post_parent = '{$book_id}' 
               order by ID desc; 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_chapter',
          'post_author'=>$author_id,
          'post_parent'=>$book_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_spreads_by_chapter_id($chapter_id){
     $chapter_id = esc_sql($chapter_id);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* 
               from {$prefix}posts 
               where post_type = 'surveyprint_spread' 
               and post_author = '{$author_id}' 
               and post_parent = '{$chapter_id}' 
               order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_spread',
          'post_author'=>$author_id,
          'post_parent'=>$chapter_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
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

function add_chapter($thread_id, $section_id, $book_id){

     $author_id = esc_sql(get_author_id());

     $thread_id = esc_sql($thread_id);
     $section_id = esc_sql($section_id);
     $book_id = esc_sql($book_id);

     $chapter = get_chapter_by_section_id($book_id, $section_id)[0];
     if(!is_null($chapter)){
          $chapter_id = $chapter->ID;
          return $chapter_id;
     }

     $title = esc_sql('no title so far');
     $description = esc_sql('no description so far');

     $uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_chapter',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$book_id,
          'post_excerpt'=>$section_id,
          'post_name'=>$uuid,
          'post_content'=>$description
     ];

     $chapter_id = init_chapter($conf);

     return $chapter_id;
}

function add_cover($thread_id, $section_id, $book_id, $chapter_id){

     $thread_id = esc_sql($thread_id);
     $section_id = esc_sql($section_id);
     $chapter_id = esc_sql($chapter_id);
     $author_id = esc_sql(get_author_id());

     $title = esc_sql('no title so far');
     $uuid = psuuid();

     $pth = Path::get_mock_dir().'/mock-spread.json';

     $doc = @file_get_contents($pth);
     $doc = json_decode($doc);
     $doc = walk_the_doc($doc);

     $doc['uuid'] = $uuid;
     $doc['panelId'] = 'cover'; 

     $doc['conf'] = [];
     $doc['conf']['max_assets'] = '0';
     $doc['conf']['layout_group'] = 'cover';

     $doc['assets'][0]['text'] = ['Example Cover of a Book'];
     $doc['assets'][1]['text'] = [];

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

function add_inside_cover($thread_id, $section_id, $book_id, $chapter_id){

     $thread_id = esc_sql($thread_id);
     $section_id = esc_sql($section_id);
     $book_id = esc_sql($book_id);
     $chapter_id = esc_sql($chapter_id);

     $author_id = esc_sql(get_author_id());
     $title = esc_sql('no title so far');
     $uuid = psuuid();

     $pth = Path::get_mock_dir().'/mock-spread.json';
     $doc = @file_get_contents($pth);
     $doc = json_decode($doc);
     $doc = walk_the_doc($doc);

     $uuid = psuuid();

     $doc['uuid'] = $uuid;
     $doc['panelId'] = 'cover'; 

     $doc['conf'] = [];
     $doc['conf']['max_assets'] = '0';
     $doc['conf']['layout_group'] = 'inside_cover';

     $doc['assets'][0]['text'] = ['Example Inside-Cover of a Book'];
     $doc['assets'][1]['text'] = [];

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

function add_intro($thread_id, $section_id, $book_id, $chapter_id){

     $thread_id = esc_sql($thread_id);
     $section_id = esc_sql($section_id);
     $book_id = esc_sql($book_id);
     $chapter_id = esc_sql($chapter_id);

     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());

     $uuid = psuuid();

     $pth = Path::get_mock_dir().'/mock-spread.json';
     $doc = @file_get_contents($pth);
     $doc = json_decode($doc);
     $doc = walk_the_doc($doc);

     $uuid = psuuid();

     $doc['uuid'] = $uuid;
     $doc['panelId'] = 'intro_page'; 

     $doc['conf'] = [];
     $doc['conf']['max_assets'] = '0';
     $doc['conf']['layout_group'] = 'intro_page';

     $doc['assets'][0]['text'] = ['Intro Page of a Book'];
     $doc['assets'][1]['text'] = [];

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

function add_toc($thread_id, $section_id, $book_id, $toc){

     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());

     $thread_id = esc_sql($thread_id);
     $section_id = esc_sql($section_id);
     $book_id = esc_sql($book_id);

     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_title'=>$title,
          'post_author'=>$author_id,
          'post_parent'=>$book_id,
          'post_name'=>psuuid(),
          'post_excerpt'=>$toc['post_excerpt'],
          'post_content'=>pigpack($toc)
     ];

     $toc_id = init_toc($conf);

     return $toc_id;
}

function add_spread($thread_id, $section_id, $panel_ref, $book_id, $chapter_id){

     $author_id = get_author_id();
     $title = 'no title so far';

     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     if(null == $panel){ 
          return false; 
     }
     $panel->post_content = pagpick($panel->post_content);

// a panel might or not be tagged by a layout preference
     $layout_group = $panel->post_content['conf']['layout_group'];
     $layout_group = is_null($layout_group) ? 'default' : $layout_group;

// a panel might or not be in a group
     $group_ref = $panel->post_content['conf']['parent'];

// image assets
     $maxx = 10;
     $uploaded_assets = get_assets_by_group($section_id, $panel_ref, $group_ref, $maxx);

// layout code :: fix diss
     $layout_code = '';
     foreach($uploaded_assets as $asset){
          $current_layout_code = $asset->post_name;
          $layout_code = strtoupper(sprintf('%s%s', $layout_code, $current_layout_code));
     }

// fixdiss :: what are we going to do once there is no assets in the group?
     if(is_null($layout_code)){ $layout_code = 'X'; }
     if('' === ($layout_code)){ $layout_code = 'X'; }

// loads layout document
     $doc = get_layout_by_group_and_rule($layout_group, $layout_code)[0];

// loads mock layout document
     if(null == $doc){
          $pth = Path::get_mock_dir().'/mock-spread.json';
          $doc = @file_get_contents($pth);
          $doc = json_decode($doc);
     }
     else {
          $doc = pagpick($doc->post_content);
     }
     $doc = walk_the_doc($doc);

     if(null == $doc){ 
          return false; 
     }

     $uuid = psuuid();
     $doc['uuid'] = $uuid;

// imprint
     $text = $panel->post_content['question'];
     if(false != preg_match('/^.{0,128}is going to read/', $text, $mtch)){
          $text = str_replace($mtch[0], '', $text);
     }
     $text = trim_for_print($text);

     $doc['assets'][0]['text'] = [$text];
     $doc['assets'][1]['text'] = [];

     $assets_of_document = [];

     $idx = 0;
     foreach($doc['assets'] as $asset){

// print_r($asset);

          if('image' != $asset['type']){ 
               $assets_of_document[]= $asset;
               continue; 
          }

          if(is_null($uploaded_assets[$idx])){
               $assets_of_document[]= $asset;
               continue; 
          }

          $current_asset = $uploaded_assets[$idx];

          $asset['src'] = eval_asset_src($current_asset);
          $asset = fit_image_asset_into_slot($doc, $asset);
          $idx++;

          $assets_of_document[]= $asset;
     }

     $doc['assets'] = $assets_of_document;

     $doc['panelId'] = $panel->ID;
     $doc['conf'] = $panel->post_content['conf'];

     $panel_ref = $panel->post_excerpt;

     $uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_spread',
          'post_author'=>$author_id,
          'post_title'=>$title,
          'post_name'=>$uuid,
          'post_parent'=>$chapter_id,
          'post_excerpt'=>$panel_ref,
          'post_content'=>pigpack($doc)
     ];

     $res = init_spread($conf);

     return $panel_ref;
}

function eval_asset_src($asset){

     $res = '';
// resource locator is a URL 
     if(filter_var($asset->post_content, FILTER_VALIDATE_URL)){
          $res = $asset->post_content;
     }
// resource locator is a base 64 chunk 
     else {
          $res = add_base_to_chunk($asset->post_content);
     }
     return $res;
}

// wild wild wild wild...
// ne chapter von der section vom thread der nur mitm bucch...
function get_chapter_by_section_id($book_id, $section_id){

     $section_id = esc_sql($section_id);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;
$sql = <<<EOD
          select {$prefix}posts.*
               from {$prefix}posts
               where post_type = 'surveyprint_chapter' 
               and post_author = '{$author_id}' 
               and post_parent = '{$book_id}' 
               and post_excerpt = '{$section_id}'
               order by ID desc
               limit 1
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);

     return $res;
}
