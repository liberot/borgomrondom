<?php defined('ABSPATH') || exit;



function bb_build_client_survey_view(){

     $client_id = bb_get_author_id();
     $ticket = bb_get_ticket_of_client($client_id)[0];
     $field = bb_get_field_by_ref($ticket->field_ref)[0];

     $rec = bb_get_rec_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $ticket->field_ref)[0];

     wp_register_script('service', Path::get_plugin_url().'/js/client/main.js', array('jquery'));
     wp_enqueue_script('service');

     wp_register_style('mg_style', Path::get_plugin_url().'/css/client/milligram.css');
     wp_enqueue_style('mg_style');

     wp_register_style('client_style', Path::get_plugin_url().'/css/client/style.css');
     wp_enqueue_style('client_style');

     $headline = esc_html(__('BookBuilder Questionnaire', 'bookbuilder'));

     $headline = esc_html(__('BookBuilder Questionnaire', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));

     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
EOD;

     $field = bb_decorate_field_title($field);

     $field->title = esc_html($field->title);
     $field->title = preg_replace('/\\\{1,}n\\\{1,}r/', '<br/>', $field->title);

     $field->description = esc_html($field->description);
     $field->description = preg_replace('/\\\{1,}n\\\{1,}r/', '<br/>', $field->description);

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
          <div class='field-description'>{$field->description}</div>
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
               $assets = bb_get_assets_by_field_ref($ticket->client_id, $ticket->thread_id, $field);
               break;
     }

     $submit_rec = esc_html(__('Submit REC', 'bookbuilder'));
     $previous_field = esc_html(__('Previous Field', 'bookbuilder'));

     echo <<<EOD

          <form class='client-input-form' method='post' action=''>
               {$buf1st}
               <input type='hidden' name='cmd' value='bb_write_rec'></input> 
               <input type='hidden' name='ticket' value='{$field->ref}'></input> 
               <div class='input-choice'><input class='btn-prev-rec' type='submit' value='{$submit_rec}'></div>
          </form>

          <form class='nav_prev_field' method='post' action=''>
               <input type='hidden' name='cmd' value='bb_nav_prev_field'></input> 
               <input type='hidden' name='ticket' value='{$field->ref}'></input> 
               <div class='input-choice'><input class='btn-submit-rec' type='submit' value='{$previous_field}'></div>
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



     echo <<<EOD
         </div>
EOD;


/*
bb_add_debug_field('rec:', $rec);
bb_add_debug_field('ticket: ', $ticket);
bb_add_debug_field('field: ', $field);
bb_flush_debug_field();
*/

}



function bb_build_yes_no_view($field, $rec){

     $yes = esc_html(__('Yes', 'bookbuilder'));
     $no = esc_html(__('No', 'bookbuilder'));

     $choices = bb_get_choices_of_field($field->ref);

     $buf1st = '';
     if(is_null($choices)){
     }
     else {
          $buf1st.= sprintf("<div class='input-choice row'>");
          foreach($choices as $choice){
                $title = esc_html(__($choice->title, 'bookbuilder'));
                $title = 'yes' == $choice->title ? $yes : $no;
                $value = $choice->ref;
                $checked = '';
                if($value == $rec->choice_ref){
                     $checked = 'checked';
                }
                $buf1st.= "<div class='block'>";
                $buf1st.= sprintf("<input type='radio' name='answer' value='%s' %s><br/>", $value, $checked);
                $buf1st.= sprintf("<label class='label' for='%s'>%s</label>", $value, $title);
                $buf1st.= '</input>';
                $buf1st.= '</div>';
          }
          $buf1st.= '</div>';
     }

     return $buf1st;
}



function bb_build_multiple_choice_view($field, $rec){

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
                $buf1st.= sprintf("<input type='radio' name='answer' value='%s' %s>", $value, $checked);
                $buf1st.= sprintf("<label class='label' for='%s'>%s</label>", $value, $title);
                $buf1st.= '</label>';
                $buf1st.= '</div>';
          }
          $buf1st.= '</div>';
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
                $buf1st.= sprintf("<input type='radio' name='answer' value='%s' %s>", $value, $checked);
                $buf1st.= sprintf("<label class='label' for='%s'>%s</label>", $value, $choice->title);
                $buf1st.= '</input>';

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



function bb_build_client_spread_view(){

     $client_id = bb_get_author_id();
     $ticket = bb_get_ticket_of_client($client_id)[0];

     wp_register_script('viewer-config', Path::get_plugin_url().'/js/spread/src/main/config-client.js');
     wp_register_script('main', Path::get_plugin_url().'/js/spread/src/main/main.js');
     wp_register_script('viewer-tools', Path::get_plugin_url().'/js/spread/src/main/module/tools/main.js');
     wp_register_script('viewer-screen', Path::get_plugin_url().'/js/spread/src/main/module/screen/main.js');
     wp_register_script('viewer-correct', Path::get_plugin_url().'/js/spread/src/main/module/screen/correct.js');
     wp_register_script('viewer-bitmap', Path::get_plugin_url().'/js/spread/src/main/module/screen/bitmap.js');
     wp_register_script('viewer-svg', Path::get_plugin_url().'/js/spread/lib/svg.js');
     wp_register_script('viewer-layout_util', Path::get_plugin_url().'/js/spread/src/main/module/util/main.js');
     wp_register_script('viewer-layout_net', Path::get_plugin_url().'/js/spread/src/main/module/net/main.js');
     wp_register_script('viewer-layout_init', Path::get_plugin_url().'/js/spread/init.js', array('jquery'));

     wp_enqueue_script('viewer-config');
     wp_enqueue_script('main');
     wp_enqueue_script('viewer-tools');
     wp_enqueue_script('viewer-bitmap');
     wp_enqueue_script('viewer-correct');
     wp_enqueue_script('viewer-screen');
     wp_enqueue_script('viewer-svg');
     wp_enqueue_script('viewer-layout_util');
     wp_enqueue_script('viewer-layout_net');
     wp_enqueue_script('viewer-layout_init');

     wp_register_style('mg_style', Path::get_plugin_url().'/css/client/milligram.css');
     wp_enqueue_style('mg_style');

     wp_register_style('client_style', Path::get_plugin_url().'/css/client/style.css');
     wp_enqueue_style('client_style');

     wp_register_style('spread_style', Path::get_plugin_url().'/css/spread/style.css');
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
          <div class='layout-edit'>
               <div class='layout-messages'></div>
               <div class='layout-main'>
                    <div class='layout-rows'>
                         <div class='layout-buff'>
                              <div class='screen'></div>
                         </div>
                    </div>
               </div>
          </div>
          <div class='layout-controls'>
               <div class='layout-pages'></div>
               <div class='layout-tools'></div>
               <div class='layout-library'></div>
               <div class='layout-actions'></div>
          </div>

          <div class='offscreen'></div>
          <div class='printscreen'></div>
EOD;



     echo <<<EOD
          <form class='input-form block' method='post' action=''>
               <input type='hidden' name='cmd' value='bb_show_survey'></input> 
               <div class=''><input type='submit' value='Close'></div>
          </form>
EOD;



     echo <<<EOD
          </div>
EOD;



}



