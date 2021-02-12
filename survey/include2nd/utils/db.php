<?php defined('ABSPATH') || exit;



function drop_tables(){

     $tables = [
          'ts_bb_survey', 'ts_bb_group', 'ts_bb_field', 'ts_bb_choice', 
          'ts_bb_thread', 'ts_bb_input', 
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
}



function init_tables(){

     init_survey_table();
     init_group_table();
     init_field_table();
     init_choice_table();

     init_thread_table();
     init_input_table();

     init_book_table();
     init_chapter_table();
     init_section_table();
     init_spread_table();
}



function init_book_table(){

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
}



function init_chapter_table(){

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
}



function init_section_table(){

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
}



function init_spread_table(){

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
}



function init_thread_table(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_thread (
               id bigint(20) unsigned not null,
               client_id bigint(20) unsigned not null,
               survey_id bigint(20) unsigned not null,
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
}



function init_input_table(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_input (
               id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               client_id bigint(20) unsigned not null,
               survey_id bigint(20) unsigned not null,
               group_id bigint(20) unsigned not null,
               field_id bigint(20) unsigned not null,
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
}



function init_survey_table(){

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
}

function init_group_table(){

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
}

function init_field_table(){

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
}

#foreign key (post_id) references {$prefix}posts(ID),
#foreign key (post_parent_id) references wp_posts(ID),



function init_choice_table(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_choice (
               id bigint(20) not null auto_increment,
               ref varchar(255) not null unique,
               typeform_ref varchar(255),
               survey_ref varchar(255),
               group_ref varchar(255),
               parent_ref varchar(255),
               field_ref varchar(255),
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



