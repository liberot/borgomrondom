<?php defined('ABSPATH') || exit;



function drop_tables(){

     $tables = ['bb_survey', 'bb_group', 'bb_field', 'bb_thread', 'bb_input', 'bb_book', 'bb_chapter', 'bb_section', 'bb_spread'];

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
          {$prefix}bb_book (
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
          {$prefix}bb_chapter (
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
          {$prefix}bb_section (
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
          {$prefix}bb_spread (
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
          {$prefix}bb_thread (
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
          {$prefix}bb_input (
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
          {$prefix}bb_survey (
               id bigint(20) unsigned not null,
               linked_survey_id bigint(20) unsigned not null,
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

function init_group_table(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}bb_group (
               id bigint(20) unsigned not null,
               parent_id bigint(20) unsigned not null,
               survey_id bigint(20) unsigned not null,
               linked_survey_id bigint(20) unsigned not null,
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

function init_field_table(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}bb_field (
               id bigint(20) unsigned not null,
               group_id bigint(20) unsigned not null,
               parent_id bigint(20) unsigned not null,
               linked_survey_id bigint(20) unsigned not null,
               linked_field_id bigint(20) unsigned not null,
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

#foreign key (post_id) references {$prefix}posts(ID),
#foreign key (post_parent_id) references wp_posts(ID),



function debug_sql($sql){

     if(true != Proc::TMP_WRITE_SQL){
          return $sql;
     }

     $sql = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $sql);
     $sql.= "\n"; 
     @file_put_contents('/tmp/sql', $sql, FILE_APPEND);

     return $sql;
}



