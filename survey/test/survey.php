<?php defined('ABSPATH') || exit;

add_action('init', 'exec_survey_test1st');
add_action('init', 'exec_survey_test2nd');
function exec_survey_test1st(){

     init_survey_utils();

     $nl = "\n";

     $conf = [];
     $conf['post_type'] = 'surveyprint_survey';
     $conf['post_author'] = get_current_user_id();
     $conf['post_title'] = sprintf('Title of a Survey %s', random_string());
     $conf['post_name'] = sprintf('Title of a Survey %s', random_string());
     $conf['meta_input'] = [
          'toc'=>'1000000000000000000000001,1000000000000000000000003'
     ];
     $conf['post_content'] = random_string();

     $survey_id = init_survey($conf);
     print_r($survey_id);
     print $nl;

     $i = 239;
     while($i--){
          $conf = [];
          $conf['post_type'] = 'surveyprint_question';
          $conf['post_parent'] = $survey_id;
          $conf['post_author'] = get_current_user_id();
          $conf['post_title'] = sprintf('Title of a Question %s %s', $i, random_string());
          $conf['post_name'] = sprintf('Excerpt of a Question %s %s', $i, random_string());
          $conf['post_content'] = pigpack([
                'type'=>'multiple_choice',
                'validation'=>'maximo',
                'choices'=>[
                      [
                           'choice'=>sprintf('1st Answer %s', random_string()),
                           'ref'=>'00000000000000001'
                      ],
                      [
                           'choice'=>sprintf('2nd Answer %s', random_string()),
                           'ref'=>'00000000000000001'
                      ],
                      [
                           'choice'=>sprintf('3rd Answer %s', random_string()),
                           'ref'=>'00000000000000001'
                      ]
                ]
          ]);
          $question_id = init_question($conf);
          print_r($question_id);
          print $nl;
     }

}

function exec_survey_test2nd(){

     print_r(get_surveys());
     print_r(get_survey_by_id('130233'));
     print_r(get_survey_by_ref('ref::p3R4VSGr'));
     print_r(get_questions_by_survey_id('131463'));
     print_r(get_surveys());
     print_r(get_survey_by_id('146548'));
     print_r(get_questions_by_survey_id('147497'));
}
