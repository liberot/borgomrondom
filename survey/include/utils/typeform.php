<?php defined('ABSPATH') || exit;

function get_typeform_surveys(){
     $author_id = esc_sql(get_author_id());
     global $wpdb;
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' and post_excerpt = 'typeform' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_typeform_surveys_by_ref($ref){

     $ref = esc_sql($ref);
     global $wpdb;
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' and post_excerpt = 'typeform' and post_name = '{$ref}' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}


