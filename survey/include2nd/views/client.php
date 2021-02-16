<?php defined('ABSPATH') || exit;



// this be started at auth
add_action('init', 'init_bb_thread');



add_shortcode('client_view', 'build_client_view');
function build_client_view(){

     process_incoming();

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
          <form class='input-form' method='post' action=''>
               <input type='hidden' name='cmd' value='reset_session'></input> 
               <div class=''><input type='submit' value='Start Thread'></div>
          </form>
EOD;

     echo <<<EOD
          <div class=''>client_id: {$client_id}</div>
          <div class=''>thread_id: {$thread_id}</div>
          <div class=''>survey_ref: {$field->survey_ref}</div>
          <div class=''>group_ref: {$field->group_ref}</div>
          <div class=''>field_ref: {$field_ref}</div>
          <div class=''>type: {$field->type}</div>
          <div class='field-title'>{$field->title}</div>
EOD;

     $buf1st = '';
     switch($field->type){

          case 'statement':

               $buf1st.= "<input type='hidden' name='answer' value='noticed'></input>"; 
               break;

          case 'short_text':

               $buf1st.= "<div class=''><input type='text' class='input-text' name='answer'></input></div>";
               break;

          case 'multiple_choice':

               $choices = get_choices_of_field($field_ref);
               if(is_null($choices)){
               }
               else {
                    $buf1st.= sprintf("<div class='input-choice'>");
                    foreach($choices as $choice){
                         $buf1st.= sprintf("<input type='radio' name='answer' value='%s'> %s</input><br/>", $choice->ref, $choice->title);
                    }
               }
               break;

          case 'yes_no':
               $buf1st.= sprintf("<input type='radio' name='answer' value='%s'> %s</input><br/>", 'true', 'Yes');
               $buf1st.= sprintf("<input type='radio' name='answer' value='%s'> %s</input><br/>", 'false', 'No');
               break;



     }

     echo <<<EOD
          <form class='input-form' method='post' action=''>
               {$buf1st}
               <input type='hidden' name='cmd' value='rec'></input> 
               <input type='hidden' name='ticket' value='{$field_ref}'></input> 
               <div class=''><input type='submit' value='Rec'></div>
          </form>
EOD;

}



