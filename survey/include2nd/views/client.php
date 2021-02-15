<?php defined('ABSPATH') || exit;



// this be started at auth
add_action('init', 'init_bb_thread');



add_shortcode('client_view', 'build_client_view');
function build_client_view(){

     set_next_field_ref();

     wp_register_style('admin_style', WP_PLUGIN_URL.SURVeY.'/css/client/style.css');
     wp_enqueue_style('admin_style');

     $headline = esc_html(__('BookBuilder Client Threads', 'bookbuilder'));
     $field_ref = get_session_ticket('field_ref');
     $field = get_field_by_ref($field_ref)[0];

     $headline = esc_html(__('BookBuilder Questionnaire', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
EOD;

     $field = decorate_field_title($field);

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');
     echo <<<EOD
          <div class=''>client_id: {$client_id}</div>
          <div class=''>thread_id: {$thread_id}</div>
          <div class=''>survey_ref: {$field->survey_ref}</div>
          <div class=''>group_ref: {$field->group_ref}</div>
          <div class=''>field_ref: {$field_ref}</div>
          <div class='field-title'>{$field->title}</div>
EOD;

     echo <<<EOD
          <form class='input-main' method='post' action=''>
          <div class=''><input type='submit'></div>
          </form>
EOD;

}



