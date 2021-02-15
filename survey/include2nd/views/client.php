<?php defined('ABSPATH') || exit;




add_shortcode('client_view', 'build_client_view');
function build_client_view(){

     $field_ref = get_session_ticket('field_ref');
     $field = get_field_by_ref($field_ref);

     $headline = esc_html(__('BookBuilder Questionnaire', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
     EOD;

     echo <<<EOD

          {$field->title}
EOD;


print "thread_id:";
print_r(get_session_ticket('thread_id'));
print "<br/>";
print "field_ref:";
print_r($field_ref);
print "<br/>";
print_r($field);
print "<br/>";

}



