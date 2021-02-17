<?php defined('ABSPATH') || exit;



add_shortcode('client_view', 'build_client_view');
function build_client_view(){

     if(!is_user_logged_in()){
          echo '<p>ProfileBuilder Authentication Procedere</p>';
          echo do_shortcode('[wppb-login]');
          echo do_shortcode('[wppb-register]');
          echo do_shortcode('[wppb-recover-password]');
          return;
     }

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');
     $field_ref = get_session_ticket('field_ref');
     $field = get_field_by_ref($field_ref)[0];
     $answer_text = '';
     $rec = get_rec_of_field($client_id, $thread_id, $field_ref)[0];
     if(is_null($rec)){
     }
     else {
debug_field_add('rec', $rec);
          $answer_text = esc_html($rec->doc);
     }

     wp_register_script('service', WP_PLUGIN_URL.SURVeY.'/js/client/main.js', array('jquery'));
     wp_enqueue_script('service');

     wp_register_style('admin_style', WP_PLUGIN_URL.SURVeY.'/css/client/style.css');
     wp_enqueue_style('admin_style');

     $headline = esc_html(__('BookBuilder Client Threads', 'bookbuilder'));

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


flush_debug_field();


     echo <<<EOD

          <div class='row'>

               <form class='input-form block' method='post' action=''>
               <input type='hidden' name='cmd' value='init_session'></input> 
               <div class=''><input type='submit' value='Start existing thread'></div>
               </form>

               <form class='input-form block' method='post' action=''>
               <input type='hidden' name='cmd' value='reset_session'></input> 
               <div class=''><input type='submit' value='Start a new thread'></div>
               </form>

          </div>

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

               $buf1st.= "<div class=''>";
               $buf1st.= sprintf("<input type='text' class='input-text' name='answer' value='%s'></input>", $answer_text);
               $buf1st.= "</div>";
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
               <div class=''><input type='submit' value='Submit REC'></div>
          </form>
EOD;

}



