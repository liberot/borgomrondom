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
$sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_book' and post_author = '{$author_id}' and ID = '{$book_id}';
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_chapter_by_book_id($book_id){
     $book_id = esc_sql($book_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_chapter' and post_author = '{$author_id}' and post_parent = '{$book_id}' order by ID desc; 
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_spreads_by_chapter_id($chapter_id){
     $chapter_id = esc_sql($chapter_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_spread' and post_author = '{$author_id}' and post_parent = '{$chapter_id}' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
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

     $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-spread.json';

     $doc = @file_get_contents($path);
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

function add_inside_cover($chapter_id, $title){

     $chapter_id = esc_sql($chapter_id);
     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());
     $uuid = psuuid();

     $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-spread.json';
     $doc = @file_get_contents($path);
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

function add_intro($chapter_id, $title){

     $chapter_id = esc_sql($chapter_id);
     $title = esc_sql(trim_for_print($title));
     $author_id = esc_sql(get_author_id());
     $uuid = psuuid();

     $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-spread.json';
     $doc = @file_get_contents($path);
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

     $author_id = get_author_id();

// https://www.php.net/manual/en/function.imageloadfont.php
// https://www.php.net/manual/en/function.imagettftext.php
// https://developer.apple.com/fonts/TrueType-Reference-Manual/RM06/Chap6fmtx.html
// https://www.php.net/manual/en/imagick.queryfontmetrics.php
// https://docs.oracle.com/javase/7/docs/api/java/awt/FontMetrics.html
// https://github.com/RazrFalcon/ttf-parser
// https://github.com/Pomax/PHP-Font-Parser
// https://github.com/qdsang/ttf2svg
// https://stackoverflow.com/questions/4190667/how-to-get-width-of-a-truetype-font-character-in-1200ths-of-an-inch-with-python
// https://stackoverflow.com/questions/2480183/get-width-of-a-single-character-from-ttf-font-in-php
// https://www.php.net/imagettfbbox

// panel might have a group as 'cover' with three panels
// groups is going to gather differnt spreads in a semantic way
// as the uploaded images of three sisters and such
     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     if(null == $panel){ return false; }
     $panel->post_content = pagpick($panel->post_content);

// a panel might or not be tagged by a layout preference
     $layout_group = $panel->post_content['conf']['layout_group'];
     $layout_group = is_null($layout_group) ? 'default' : $layout_group;

// todo: debug: layout_code is
     $layout_code = 'U';

// loads layout document
     $doc = get_layout_by_group_and_rule($layout_group, $layout_code)[0];

     if(null == $doc){
          $path = WP_PLUGIN_DIR.SURVeY.'/asset/layout-draft/mock-spread.json';
          $doc = @file_get_contents($path);
          $doc = json_decode($doc);
          $doc = walk_the_doc($doc);
     }
     else {
          $doc = pagpick($doc->post_content);
          $doc = walk_the_doc($doc);
     }

     if(null == $doc){ return false; }

     $uuid = psuuid();
     $doc['uuid'] = $uuid;

// answer
     $text = trim_for_print($panel->post_content['answer']);

     $doc['assets'][0]['text'] = [$text];
     $doc['assets'][1]['text'] = [];

// assets
     $maxx = 1;
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
          $uploaded_asset = get_assets_by_panel_ref($section_id, $panel->post_excerpt, $maxx)[0];

print('>>>>>');
print_r($uploaded_assets);

          if(null != $uploaded_asset){
               $asset['src'] = eval_asset_src($uploaded_asset->post_content);
               $asset = fit_image_asset_into_slot($doc, $asset);
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

function eval_asset_src($post){
print ">>";
print_r($post);
     $res = '';
     if(filter_var($post, FILTER_VALIDATE_URL)){
          $res = $post;
     }
     else {
          $res = add_base_to_chunk($post);
     }
     return $res;
}
