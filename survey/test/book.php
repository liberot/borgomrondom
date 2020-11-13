<?php defined('ABSPATH') || exit;

add_action('init', 'exec_book_test1st');
add_action('init', 'exec_book_test2nd');
add_action('init', 'exec_book_test3rd');
function exec_book_test1st(){

     init_book_utils();

     $nl = "\n";

     $conf = [];
     $conf['post_type'] = 'surveyprint_book';
     $conf['post_author'] = get_current_user_id();
     $conf['post_title'] = sprintf('Title of a Book %s', random_string(32));
     $conf['post_name'] = sprintf('Title of a Book %s', random_string(32));
     $conf['meta_input'] = [
          'chapters'=>'11111111111000000000001,20000000000000000000000,11111111199999999999999'
     ];

     $book_id = init_book($conf);
     print_r($book_id);
     print $nl;

     $i = 13;
     while($i--){
          $conf = [];
          $conf['post_type'] = 'surveyprint_chapter';
          $conf['post_parent'] = $book_id;
          $conf['post_author'] = get_current_user_id();
          $conf['post_title'] = sprintf('Title of a Chapter %s %s', $i, random_string());
          $conf['post_name'] = random_string(32);
          $conf['meta_input'] = [
               'spreads'=>'11111111111000000000001,20000000000000000000000,11111111199999999999999'
          ];

          $chapter_id = init_chapter($conf);
          print_r($chapter_id);
          print $nl;

          $ii = 107;
          while($ii--){
               $conf = [];
               $conf['post_type'] = 'surveyprint_spread';
               $conf['post_author'] = get_current_user_id();
               $conf['post_parent'] = $chapter_id;
               $conf['post_title'] = sprintf('Title of a Spread %s %s', $i, random_string());
               $conf['post_name'] = random_string(32);
               
               $spread_id = init_spread($conf);
               print_r($spread_id);
               print $nl;
          }
     }
}

function exec_book_test2nd(){

     print_r(get_books());
     print_r(get_book_by_id('128828'));
     print_r(get_chapter_by_book_id('128828'));
     print_r(get_spreads_by_chapter_id('128937'));
}

function exec_book_test3rd(){

     print_r(get_books());
     print_r(get_book_by_id('141842'));
     print_r(get_chapter_by_book_id('141842'));
     print_r(get_spreads_by_chapter_id('141951'));
}
