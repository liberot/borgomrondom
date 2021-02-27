<?php defined('ABSPATH') || exit;



function bb_build_client_survey_view($ticket){

     $client_id = bb_get_author_id();

     $field = bb_get_field_by_ref($ticket->field_ref)[0];
     $rec = bb_get_rec_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $ticket->field_ref)[0];
//bb_add_debug_field('rec:', $rec);

     wp_register_script('service', Path::get_plugin_url().'/js/client/main.js', array('jquery'));
     wp_enqueue_script('service');

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
//bb_add_debug_field('field:', $field);

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

               <form class='input-form block' method='post' action=''>
                     <input type='hidden' name='cmd' value='bb_show_spreads'></input> 
                     <div class=''><input type='submit' value='Show spreads'></div>
               </form>

          </div>
EOD;

     echo <<<EOD
          <div class='field-title'>{$field->description}</div>
          <div class='field-title'>{$field->title}</div>
EOD;

     $buf1st = '';
     $assets = null;

     switch($field->type){

          case 'statement':

               $buf1st = bb_build_statement_view($field, $rec);

               break;

          case 'short_text':
          case 'number':

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
               $assets = bb_get_assets_by_field_ref($ticket->client_id, $ticket->thread_id, $field->ref);

               break;
     }

     echo <<<EOD
          <form class='client-input-form' method='post' action=''>
               {$buf1st}
               <input type='hidden' name='cmd' value='bb_write_rec'></input> 
               <input type='hidden' name='ticket' value='{$field->ref}'></input> 
               <div class=''><input class='btn-submit-rec' type='submit' value='Submit REC'></div>
          </form>
EOD;

     echo "<script type='text/javascript'>";
     echo "let = assetsOfField = ";
     if(!is_null($assets)){
          echo json_encode($assets, JSON_PRETTY_PRINT);
     }
     else{
          echo "[]";
     }
     echo ';';
     echo PHP_EOL;
     echo "</script>";

/***
     echo <<<EOD
          <div class='debug-out'>
               <div class=''>client_id: {$ticket->client_id}</div>
               <div class=''>thread_id: {$ticket->thread_id}</div>
               <div class=''>rec_pos: {$ticket->rec_pos}</div>
               <div class=''>survey_ref: {$field->survey_ref}</div>
               <div class=''>group_ref: {$field->group_ref}</div>
               <div class=''>field_ref: {$field->ref}</div>
               <div class=''>type: {$field->type}</div>
EOD;
***/

//bb_flush_debug_field();

     echo <<<EOD
          </div>
EOD;

}



function bb_build_yes_no_view($field, $rec){

     $yes = esc_html(__('Yes', 'bookbuilder'));
     $no = esc_html(__('No', 'bookbuilder'));

     $choices = bb_get_choices_of_field($field->ref);

     $buf1st = '';
     if(is_null($choices)){
     }
     else {
          $buf1st.= sprintf("<div class='input-choice'>");
          foreach($choices as $choice){
                $title = esc_html(__($choice->title, 'bookbuilder'));
                $value = $choice->ref;
                $checked = '';
                if($value == $rec->choice_ref){
                     $checked = 'checked';
                }
                $buf1st.= sprintf("<input type='radio' name='answer' value='%s' %s> %s</input><br/>", $value, $checked, $title);
          }
     }

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

                $title = esc_html($choice->title);
                $value = esc_html($choice->ref);
                $checked = '';
                if($title == $rec->doc){
                     $checked = 'checked';
                }

                $buf1st.= sprintf("<input type='radio' name='answer' value='%s' %s> %s</input><br/>", $value, $checked, $title);
          }
     }
     return $buf1st;
}



function bb_build_short_text_view($field, $rec){

     $answer = esc_html($rec->doc);

     $buf1st = '';
     $buf1st.= "<div class=''>";
     $buf1st.= sprintf("<input type='text' class='input-text' name='answer' value='%s'></input>", $answer);
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

                $title = esc_html($choice->title);
                $value = esc_html($choice->ref);
                $checked = '';
                if($title == $rec->doc){
                     $checked = 'checked';
                }

                $buf1st.= "<div class='block'>";
                $buf1st.= sprintf("<input type='radio' name='answer' value='%s' %s> %s</input><br/>", $value, $checked, $choice->title);

                $temp = json_decode(base64_decode($choice->doc, true));
                if(is_null($temp)){
                }
                else {
                     $buf1st.= sprintf("<img class='image-choice' src='%s'>", $temp->attachment->href);
                }
                $buf1st.= "</div>";
          }
          $buf1st.= "</div>";
     }
     return $buf1st;
}



function bb_build_file_upload_view($field, $rec){

     $drop_those_files = esc_html(__('Drop The Files Into Here', 'bookbuilder'));
     
     $buf1st = <<<EOD
     <input type='file' class='files' name='filename' multiple='multiple' accept='image/jpeg, image/png'></input>
     <div class='fake'>{$drop_those_files}</div>

     <div class='row'>
          <div class='asset-copies'></div>
     </div>
EOD;

     return $buf1st;
}



function bb_build_client_spread_view($ticket){

     wp_register_style('spread_style', Path::get_plugin_url().'/css/client/spread.css');
     wp_enqueue_style('spread_style');

     $headline = esc_html(__('BookBuilder Spreads', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
EOD;

     echo <<<EOD
          <div class=''>The spreads be shown here as for debug reasons</div>

          <form class='input-form block' method='post' action=''>
               <input type='hidden' name='cmd' value='bb_show_survey'></input> 
               <div class=''><input type='submit' value='Switch to survey'></div>
          </form>

EOD;

}



