<?php defined('ABSPATH') || exit;

add_action('init', 'init_layout_utils');
function init_layout_utils(){
     $res = register_post_type(
          'surveyprint_layout',  [
               'label'                  =>'SurveyPrint Layout',
               'description'            =>'SurveyPrint Layout',
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

function init_layout($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_layouts_by_group($group){
     // $res = get_posts(array('tag' => array($tags)));
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_layout' and post_title = '{$group}' order by ID desc;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_layout_presets_by_group_and_rule($group, $rule){
     // $res = get_posts(array('tag' => array($tags)));
     $sql = <<<EOD
          select * from wp_posts 
               where post_type = 'surveyprint_layout' 
               and post_title = '{$group}' 
               and post_excerpt = '{$rule}' 
               order by ID desc;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_layouts_by_tags($tags){
     $res = get_posts(array('tag' => array($tags)));
     $sql = <<<EOD
          select p.ID, t.term_id, t.name
               from wp_posts p, wp_term_relationships r, wp_terms t
               where p.post_type = 'surveyprint_layout'
               and r.term_taxonomy_id = t.term_id
               and p.ID = r.object_id
               and p.ID = 29823;
EOD;
     $sql = debug_sql($sql);
     $sql = <<<EOD
          select p.ID, p.post_title, t.name
               from wp_posts p, wp_term_relationships r, wp_terms t
               where p.post_type = 'surveyprint_layout'
               and r.term_taxonomy_id = t.term_id
               and p.ID = r.object_id
               and t.name like '%th%';

EOD;
     $sql = debug_sql($sql);
};


