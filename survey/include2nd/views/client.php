<?php defined('ABSPATH') || exit;



add_shortcode('bb_client_view', 'bb_build_client_view');
function bb_build_client_view(){

     wp_register_script('service', Path::get_plugin_url().'/js/client/main.js', array('jquery'));
     wp_enqueue_script('service');

     if(!is_user_logged_in()){
          echo '<p>ProfileBuilder Authentication Procedere</p>';
          echo do_shortcode('[wppb-login]');
          echo do_shortcode('[wppb-register]');
          echo do_shortcode('[wppb-recover-password]');
          return;
     }

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');
     $field_ref = bb_get_session_ticket('field_ref');

     $field = bb_get_field_by_ref($field_ref)[0];

     $rec = bb_get_rec_of_client_by_field_ref($client_id, $thread_id, $field_ref)[0];

     wp_register_style('client_style', Path::get_plugin_url().'/css/client/style.css');
     wp_enqueue_style('client_style');

     $headline = esc_html(__('BookBuilder Questionnaire', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
EOD;

     $field = bb_decorate_field_title($field);

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');
     $rec_pos = bb_get_session_ticket('rec_pos');


bb_flush_debug_field();

     echo <<<EOD

          <div class='row'>

               <form class='input-form block' method='post' action=''>
               <input type='hidden' name='cmd' value='bb_init_existing_thread'></input> 
               <div class=''><input type='submit' value='Start existing thread'></div>
               </form>

               <form class='input-form block' method='post' action=''>
               <input type='hidden' name='cmd' value='bb_init_new_thread'></input> 
               <div class=''><input type='submit' value='Start a new thread'></div>
               </form>

          </div>

EOD;

     echo <<<EOD
          <div class=''>client_id: {$client_id}</div>
          <div class=''>thread_id: {$thread_id}</div>
          <div class=''>rec_pos: {$rec_pos}</div>
          <div class=''>survey_ref: {$field->survey_ref}</div>
          <div class=''>group_ref: {$field->group_ref}</div>
          <div class=''>field_ref: {$field_ref}</div>
          <div class=''>type: {$field->type}</div>
          <div class='field-title'>{$field->title}</div>
EOD;

     $buf1st = '';
     switch($field->type){

          case 'statement':

               $buf1st = bb_build_statement_view($field, $rec);
               break;

          case 'short_text':

               $buf1st = bb_build_short_text_view($field, $rec);
               break;

          case 'multiple_choice':

               $buf1st = bb_build_multiple_choice_view($field, $rec);
               break;

          case 'yes_no':
               $buf1st = bb_build_yes_no_view($field, $rec);
               break;

          case 'picture_choice':
               $buf1st = bb_build_picture_choice_view($field, $rec);
               break;

          case 'file_upload':
               $buf1st = bb_build_file_upload_view($field, $rec);
               break;

     }

     echo <<<EOD
          <form class='input-form' method='post' action=''>
               {$buf1st}
               <input type='hidden' name='cmd' value='bb_write_rec'></input> 
               <input type='hidden' name='ticket' value='{$field_ref}'></input> 
               <div class=''><input type='submit' value='Submit REC'></div>
          </form>
EOD;

}



function bb_build_yes_no_view($field, $rec){

     $yes = esc_html(__('Yes', 'bookbuilder'));
     $no = esc_html(__('No', 'bookbuilder'));

     $buf1st = '';
     $buf1st.= sprintf("<input type='radio' name='answer' value='%s'> %s</input><br/>", 'true', $yes);
     $buf1st.= sprintf("<input type='radio' name='answer' value='%s'> %s</input><br/>", 'false', $no);

     return $buf1st;
}



function bb_build_multiple_choice_view($field, $rec){

     $buf1st = '';
     $choices = bb_get_choices_of_field($field->ref);
     if(is_null($choices)){
     }
     else {
          $buf1st.= sprintf("<div class='input-choice'>");
          foreach($choices as $choice){
                $buf1st.= sprintf("<input type='radio' name='answer' value='%s'> %s</input><br/>", $choice->ref, $choice->title);
          }
     }
     return $buf1st;
}



function bb_build_short_text_view($field, $rec){

     $answer_text = '';
     if(is_null($rec)){
     }
     else {
          $answer_text = esc_html($rec->doc);
     }

     $buf1st = '';
     $buf1st.= "<div class=''>";
     $buf1st.= sprintf("<input type='text' class='input-text' name='answer' value='%s'></input>", $answer_text);
     $buf1st.= "</div>";
     return $buf1st;
}



function bb_build_statement_view($field, $rec){

     $buf1st = '';
     $buf1st.= "<input type='hidden' name='answer' value='noticed'></input>"; 
     return $buf1st;
}



function bb_build_picture_choice_view($field, $rec){

     $buf1st = '';
     $choices = bb_get_choices_of_field($field->ref);
     if(is_null($choices)){
     }
     else {
          $buf1st.= sprintf("<div class='input-choice row'>");
          foreach($choices as $choice){
                $buf1st.= "<div class='block'>";
                $buf1st.= sprintf("<input type='radio' name='answer' value='%s'> %s</input><br/>", $choice->ref, $choice->title);
                $temp = json_decode(base64_decode($choice->doc, true));
                if(is_null($temp)){
                }
                else {
                     $buf1st.= sprintf("<img class='image-choice' src='%s'>", $temp->attachment->href);
                }
                $buf1st.= "</div>";
          }
     }
     return $buf1st;
}



function bb_build_file_upload_view($field, $rec){

     $drop_those_files = esc_html(__('Drop The Files Into Here', 'bookbuilder'));

     $buf1st = <<<EOD
     <form>
          <input type='file' class='files' name='filename' multiple='multiple' accept='image/jpeg, image/png'></input>
          <div class='fake'>{$drop_those_files}</div>
     </form>

     <div class='row'>
          <div class='asset-copies'></div>
     </div>
EOD;

     return $buf1st;
}



