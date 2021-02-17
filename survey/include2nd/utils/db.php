<?php defined('ABSPATH') || exit;



function remove_v1_records(){

     $res = false;

     $types = [
          'surveyprint_asset',
          'surveyprint_book',
          'surveyprint_chapter',
          'surveyprint_layout',
          'surveyprint_panel',
          'surveyprint_question',
          'surveyprint_section',
          'surveyprint_spread',
          'surveyprint_survey',
          'surveyprint_thread',
          'surveyprint_toc',
          'surveyprint_log'
     ];

     global $wpdb;
     $prefix = $wpdb->prefix;
     foreach($types as $type){
          $sql = <<<EOD
               delete from {$prefix}posts where post_type = '{$type}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->query($sql);
     }
     return $res;
}



function drop_tables(){

     $res = false;

     $tables = [
          'ts_bb_survey', 'ts_bb_group', 'ts_bb_field', 'ts_bb_choice', 'ts_bb_action',
          'ts_bb_thread', 'ts_bb_input', 'ts_bb_rec', 
          'ts_bb_book', 'ts_bb_chapter', 'ts_bb_section', 'ts_bb_spread'
     ];

     global $wpdb;
     foreach($tables as $table){

          $prefix = $wpdb->prefix;
          $sql = <<<EOD
               drop table if exists {$prefix}$table
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->query($sql);
     }

     return $res;
}



function init_tables(){

     $res = init_survey_table();
     $res&= init_group_table();
     $res&= init_field_table();
     $res&= init_choice_table();
     $res&= init_action_table();

     $res&= init_thread_table();
     $res&= init_rec_table();

     $res&= init_book_table();
     $res&= init_chapter_table();
     $res&= init_section_table();
     $res&= init_spread_table();

     return $res;
}



function init_book_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_book (
               id bigint(20) unsigned not null,
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               title varchar(255) null,
               note varchar(255) null,
               description varchar(255),
               init datetime,
               content text,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function init_chapter_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_chapter (
               id bigint(20) unsigned not null,
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               book_id bigint(20) unsigned not null,
               pos int unsigned not null,
               title varchar(255) null,
               note varchar(255) null,
               description varchar(255),
               init datetime,
               content text,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function init_section_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_section (
               id bigint(20) unsigned not null,
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               book_id bigint(20) unsigned not null,
               chapter_id bigint(20) unsigned not null,
               pos int unsigned not null,
               title varchar(255) null,
               note varchar(255) null,
               description varchar(255),
               init datetime,
               content text,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function init_spread_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_spread (
               id bigint(20) unsigned not null,
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               book_id bigint(20) unsigned not null,
               chapter_id bigint(20) unsigned not null,
               section_id bigint(20) unsigned not null,
               pos int unsigned not null,
               title varchar(255) null,
               note varchar(255) null,
               description varchar(255),
               init datetime,
               content text,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function init_thread_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_thread (
               id bigint(20) not null auto_increment,
               client_id bigint(20) unsigned not null,
               title varchar(255) null,
               note varchar(255) null,
               description varchar(255),
               init datetime,
               content text,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function init_rec_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_rec (
               id bigint(20) not null auto_increment,
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               survey_ref varchar(255),
               group_ref varchar(255),
               field_ref varchar(255),
               title varchar(255),
               note varchar(255),
               description varchar(255),
               init datetime,
               doc text,
               pos int,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function init_survey_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_survey (
               id bigint(20) not null auto_increment,
               ref varchar(255) not null unique,
               title varchar(255),
               headline varchar(255),
               linked_survey_id bigint(20) unsigned,
               linked_survey_ref bigint(20) unsigned,
               description varchar(255),
               init datetime,
               doc text,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}

function init_group_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_group (
               id bigint(20) not null auto_increment,
               ref varchar(255) not null unique,
               typeform_ref varchar(255)not null,
               parent_ref varchar(255) not null,
               survey_ref varchar(255) not null,
               linked_survey_ref varchar(255),
               title varchar(255),
               description varchar(255),
               init datetime,
               doc text,
               layout_def varchar(255),
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}

function init_field_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_field (
               id bigint(20) not null auto_increment,
               ref varchar(255) not null unique,
               typeform_ref varchar(255),
               parent_ref varchar(255),
               group_ref varchar(255),
               survey_ref varchar(255),
               linked_survey_ref varchar(255),
               linked_field_ref varchar(255),
               type varchar(255),
               init datetime,
               title text,
               description text,
               doc text,
               pos int not null,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}

#foreign key (post_id) references {$prefix}posts(ID),
#foreign key (post_parent_id) references wp_posts(ID),



function init_choice_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_choice (
               id bigint(20) not null auto_increment,
               ref varchar(255) not null unique,
               parent_ref varchar(255),
               survey_ref varchar(255),
               group_ref varchar(255),
               field_ref varchar(255),
               target_survey_ref varchar(255),
               target_field_ref varchar(255),
               title text(255),
               description text,
               doc text,
               pos int not null,
               init datetime,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function init_action_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_action (
               id bigint(20) not null auto_increment,
               ref varchar(255) not null unique,
               survey_ref varchar(255),
               field_ref varchar(255),
               cmd varchar(255),
               type varchar(255),
               link_type varchar(255),
               link_ref varchar(255),
               doc text,
               init datetime,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function debug_sql($sql){

     if(true != Proc::TMP_WRITE_SQL){
          return $sql;
     }

     $sql = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $sql);
     $sql.= "\n"; 
     @file_put_contents('/tmp/sql', $sql, FILE_APPEND);

     return $sql;
}



function get_field_by_ref($ref) {

     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
          where ref = '{$ref}' 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_actions_of_field_by_ref($ref){

     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_action 
          where field_ref = '{$ref}' 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_field_of_survey_at_pos($survey_ref, $pos){

     $survey_ref = esc_sql($survey_ref);
     $pos = esc_sql($pos);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
          where survey_ref = '{$survey_ref}' 
          and pos = '{$pos}' 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_rec_of_field($client_id, $thread_id, $field_ref){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_rec where client_id = '{$client_id}' 
               and thread_id = '{$thread_id}' 
               and field_ref = '{$field_ref}'
          order by init desc
          limit 1 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_choices_of_field($field_ref){

     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_choice where field_ref = '{$field_ref}' 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function insert_bb_rec($client_id, $thread_id, $field, $answer){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $survey_ref = esc_sql($field->survey_ref);
     $group_ref = esc_sql($field->group_ref);
     $field_ref = esc_sql($field->ref);
     $answer = esc_sql($answer);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_rec
               (client_id, thread_id, survey_ref, group_ref, field_ref, doc, init) 
          values 
               ('{$client_id}', '{$thread_id}', '{$survey_ref}', '{$group_ref}', '{$field_ref}', '{$answer}', now())
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function get_survey_by_ref($ref) {

     $res = [];
     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;


// surveys
     $sql = <<<EOD
          select * from {$prefix}ts_bb_survey where ref = '{$ref}'
EOD;
     $res['survey'] = $wpdb->get_results($sql);



// groups
     $sql = <<<EOD
          select * from {$prefix}ts_bb_group where survey_ref = '{$ref}'
EOD;
     $res['groups'] = $wpdb->get_results($sql);



// fields
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field where survey_ref = '{$ref}'
          order by pos
EOD;
     $res['fields'] = $wpdb->get_results($sql);



// choices
     foreach($res['fields'] as $field){

          $field_ref = $field->ref;
          $field->choices = [];
          $sql = <<<EOD
               select * from {$prefix}ts_bb_choice where field_ref = '{$field_ref}'
               order by pos
EOD;
          $field->choices = $wpdb->get_results($sql);
     }

     return $res;

}



function get_typeform_surveys(){

     global $wpdb;

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_survey
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_first_field_of_group($group_ref){

     $group_ref = esc_sql($group_ref);
     global $wpdb;

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
          where group_ref = '{$group_ref}'
          order by pos asc
          limit 1
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_first_field_of_survey_by_ref($survey_ref){

     $survey_ref = esc_sql($survey_ref);
     global $wpdb;

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
          where survey_ref = '{$survey_ref}'
          order by pos asc
          limit 1
EOD;

     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



/**
     adds a new thread
*/
function insert_thread($client_id){

     $client_id = esc_sql($client_id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_thread (client_id) values ('{$client_id}');
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);
     return $wpdb->insert_id;
}



function get_thread_by_id($id){

     $id = esc_sql($id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_thread where id = '{$id}';
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_session_of_client($client_id){

     $client_id = esc_sql($client_id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_thread where client_id = '{$clientid}'
          order by init desc
          limit 1
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function get_last_record_of_client($client_id, $thread_id){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_rec where client_id = '{$client_id}'
               and thread_id = '{$thread_id}'
          order by init desc
          limit 1
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

}
