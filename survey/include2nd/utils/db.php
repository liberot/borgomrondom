<?php defined('ABSPATH') || exit;



function remove_v1_posts(){

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
          'surveyprint_toc'
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
               id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               client_id bigint(20) unsigned not null,
               survey_ref varchar(255),
               group_ref varchar(255),
               field_ref varchar(255),
               title varchar(255),
               note varchar(255),
               description varchar(255),
               init datetime,
               content text,
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
               typeform_ref varchar(255),
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



