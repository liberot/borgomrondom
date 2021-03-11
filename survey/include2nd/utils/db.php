<?php defined('ABSPATH') || exit;



function bb_remove_v1_records(){

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
          'surveyprint_ticket',
          'surveyprint_toc',
          'surveyprint_log'
     ];

     global $wpdb;
     $prefix = $wpdb->prefix;
     foreach($types as $type){
          $sql = <<<EOD
               delete from {$prefix}posts where post_type = '{$type}'
EOD;
          $sql = bb_debug_sql($sql);
          $res = $wpdb->query($sql);
     }
     return $res;
}



function bb_drop_tables(){

     $res = false;

     $tables = [
          'ts_bb_conf',
          'ts_bb_survey', 'ts_bb_group', 'ts_bb_field', 'ts_bb_choice', 'ts_bb_action', 'ts_bb_hidden',
          'ts_bb_ticket',
          'ts_bb_thread', 'ts_bb_rec', 'ts_bb_asset',
          'ts_bb_book', 'ts_bb_chapter', 'ts_bb_section', 'ts_bb_layout', 'ts_bb_spread'
     ];

     global $wpdb;
     foreach($tables as $table){

          $prefix = $wpdb->prefix;
          $sql = <<<EOD
               drop table if exists {$prefix}$table
EOD;
          $sql = bb_debug_sql($sql);
          $res = $wpdb->query($sql);
     }

     return $res;
}



function bb_init_tables(){

     $res = bb_init_survey_table();
     $res&= bb_init_group_table();
     $res&= bb_init_field_table();
     $res&= bb_init_choice_table();
     $res&= bb_init_action_table();
     $res&= bb_init_hidden_table();

     $res&= bb_init_thread_table();
     $res&= bb_init_ticket_table();
     $res&= bb_init_rec_table();

     $res&= bb_init_asset_table();

     $res&= bb_init_book_table();
     $res&= bb_init_chapter_table();
     $res&= bb_init_section_table();
     $res&= bb_init_layout_table();
     $res&= bb_init_spread_table();

     $res&= bb_init_conf_table();

     return $res;
}



function bb_init_book_table(){

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
               doc longtext,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_chapter_table(){

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
               title varchar(255),
               note varchar(255),
               description varchar(255),
               pos int unsigned not null,
               init datetime,
               doc longtext,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_section_table(){

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
               doc longtext,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_spread_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_spread (
               id bigint(20) not null auto_increment,
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               book_id bigint(20),
               chapter_id bigint(20),
               section_id bigint(20),
               field_ref varchar(255),
               group_ref varchar(255),
               survey_ref varchar(255),
               title varchar(255),
               note varchar(255),
               description varchar(255),
               pos int unsigned not null,
               init datetime,
               doc longtext,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_conf_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_conf (
               id bigint(20) not null auto_increment,
               root_survey_title varchar(255),
               init datetime,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_layout_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_layout (
               id bigint(20) not null auto_increment,
               `group` varchar(255),
               code varchar(255),
               title varchar(255),
               origin varchar(255),
               description varchar(255),
               note varchar(255),
               init datetime,
               doc longtext,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_init_thread_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_thread (
               id bigint(20) not null auto_increment,
               client_id bigint(20) unsigned not null,
               title varchar(255),
               note varchar(255),
               description varchar(255),
               doc longtext,
               init datetime,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_ticket_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_ticket (
               id bigint(20) not null auto_increment,
               client_id bigint(20) unsigned not null,
               title varchar(255),
               note varchar(255),
               description varchar(255),
               init datetime,
               thread_id bigint(20) unsigned not null,
               field_ref varchar(255),
               view_state varchar(255),
               rec_pos int unsigned not null,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_rec_table(){

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
               choice_ref varchar(255),
               title varchar(255),
               note varchar(255),
               description varchar(255),
               init datetime,
               doc longtext,
               pos int unsigned not null,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_asset_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_asset (
               id bigint(20) not null auto_increment,
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               survey_ref varchar(255) not null,
               group_ref varchar(255) not null,
               field_ref varchar(255) not null,
               layout_code varchar(255) not null,
               width int unsigned not null,
               height int unsigned not null,
               rec_pos int unsigned not null,
               title varchar(255),
               note varchar(255),
               description varchar(255),
               init datetime,
               doc longtext,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_init_survey_table(){

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
               linked_survey_ref varchar(255),
               description varchar(255),
               init datetime,
               doc longtext,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}

function bb_init_group_table(){

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
               title varchar(255),
               description varchar(255),
               init datetime,
               doc longtext,
               layout_def varchar(255),
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}

function bb_init_field_table(){

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
               type varchar(255),
               init datetime,
               title longtext,
               description text,
               doc text,
               pos int unsigned not null,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}

#foreign key (post_id) references {$prefix}posts(ID),
#foreign key (post_parent_id) references wp_posts(ID),



function bb_init_choice_table(){

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
               title varchar(255),
               description varchar(255),
               doc longtext,
               pos int unsigned not null,
               init datetime,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;
}



function bb_init_action_table(){

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
               doc longtext,
               init datetime,
               primary key (id)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_init_hidden_table(){

     $res = false;

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     create table if not exists
          {$prefix}ts_bb_hidden (
               client_id bigint(20) unsigned not null,
               thread_id bigint(20) unsigned not null,
               title varchar(255) not null,
               doc longtext not null,
               survey_ref varchar(255),
               note varchar(255),
               init datetime,
               primary key (client_id, thread_id, title)
          )
          engine=innodb
          default charset='utf8'
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_debug_sql($sql){

     if(true != Proc::TMP_WRITE_SQL){
          return $sql;
     }

     $sql = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $sql);
     $sql.= "\n"; 
     @file_put_contents('/tmp/sql', $sql, FILE_APPEND);

     return $sql;
}



function bb_get_field_by_ref($ref) {

     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
               where ref = '%s' 
EOD;
     $sql = $wpdb->prepare($sql, $ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_actions_of_field_by_ref($ref){

     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_action 
               where field_ref = '%s' 
EOD;
     $sql = $wpdb->prepare($sql, $ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_field_of_survey_at_pos($survey_ref, $pos){

     $survey_ref = esc_sql($survey_ref);
     $pos = esc_sql($pos);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
               where survey_ref = '%s' 
               and pos = '%s' 
EOD;
     $sql = $wpdb->prepare($sql, $survey_ref, $pos);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_rec_of_client_by_field_ref($client_id, $thread_id, $field_ref){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_rec where client_id = '%s' 
               and thread_id = '%s' 
               and field_ref = '%s'
          order by init desc
          limit 1 
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $field_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_rec_of_client_by_rec_pos($client_id, $thread_id, $rec_pos){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $rec_pos = esc_sql($rec_pos);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_rec where client_id = '%s' 
               and thread_id = '%s' 
               and pos = '%s'
          order by init desc
          limit 1 
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $rec_pos);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_choices_of_field($field_ref){

     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_choice where field_ref = '%s' 
EOD;
     $sql = $wpdb->prepare($sql, $field_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_insert_rec($client_id, $thread_id, $rec_pos, $field, $choice_ref, $answer){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $rec_pos = esc_sql($rec_pos);

     $survey_ref = esc_sql($field->survey_ref);
     $group_ref = esc_sql($field->group_ref);
     $field_ref = esc_sql($field->ref);

     $choice_ref = esc_sql($choice_ref);
     $answer = esc_sql($answer);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_rec
               (client_id, thread_id, survey_ref, group_ref, field_ref, choice_ref, pos, doc, init)
          values 
               (
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    now()
               )
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $survey_ref, $group_ref, $field_ref, $choice_ref, $rec_pos, $answer);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_insert_asset($client_id, $thread_id, $rec_pos, $field, $scan){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);

     $survey_ref = esc_sql($field->survey_ref);
     $group_ref = esc_sql($field->group_ref);
     $field_ref = esc_sql($field->ref);

     $doc = esc_sql($scan['base']);
     $index = esc_sql($scan['index']);
     $width = esc_sql($scan['width']);
     $height = esc_sql($scan['height']);
     $layout_code = esc_sql($scan['layout_code']);

     $rec_pos = esc_sql($rec_pos);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_asset
               (
               client_id, thread_id, survey_ref, group_ref, field_ref, 
               rec_pos, title, width, height, layout_code,
               doc, init
               )
          values 
               (
               '%s', '%s', '%s', '%s', '%s', 
               '%s', '%s', '%s', '%s', '%s',
               '%s', now()
               )
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $survey_ref, $group_ref, $field_ref, $rec_pos, $index, $width, $height, $layout_code, $doc);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_get_survey_by_ref($ref) {

     $res = [];
     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;


// surveys
     $sql = <<<EOD
          select * from {$prefix}ts_bb_survey where ref = '%s'
EOD;
     $sql = $wpdb->prepare($sql, $ref);
     $sql = bb_debug_sql($sql);
     $res['survey'] = $wpdb->get_results($sql);



// groups
     $sql = <<<EOD
          select * from {$prefix}ts_bb_group where survey_ref = '%s'
EOD;
     $sql = $wpdb->prepare($sql, $ref);
     $sql = bb_debug_sql($sql);
     $res['groups'] = $wpdb->get_results($sql);



// fields
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field where survey_ref = '%s'
          order by pos
EOD;
     $sql = $wpdb->prepare($sql, $ref);
     $sql = bb_debug_sql($sql);
     $res['fields'] = $wpdb->get_results($sql);



// choices
     foreach($res['fields'] as $field){

          $field_ref = $field->ref;
          $field->choices = [];
          $sql = <<<EOD
               select * from {$prefix}ts_bb_choice where field_ref = '%s'
               order by pos
EOD;
          $sql = $wpdb->prepare($sql, $field_ref);
          $sql = bb_debug_sql($sql);
          $field->choices = $wpdb->get_results($sql);
     }

     return $res;
}



function bb_get_typeform_surveys(){

     global $wpdb;

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_survey
EOD;

     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_first_field_of_group($group_ref){

     $group_ref = esc_sql($group_ref);
     global $wpdb;

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
               where group_ref = '%s'
          order by pos asc
          limit 1
EOD;
     $sql = $wpdb->prepare($sql, $group_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_first_field_of_survey_by_ref($survey_ref){

     $survey_ref = esc_sql($survey_ref);
     global $wpdb;

     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_field 
               where survey_ref = '%s'
          order by pos asc
          limit 1
EOD;
     $sql = $wpdb->prepare($sql, $survey_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_insert_thread($client_id){

     $client_id = esc_sql($client_id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_thread (client_id, init) values ('%s', now());
EOD;
     $sql = $wpdb->prepare($sql, $client_id);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $wpdb->insert_id;
}



function bb_get_thread_by_id($client_id, $thread_id){

     $id = esc_sql($id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_thread 
               where id = '%s'
               and client_id = '%s'
EOD;
     $sql = $wpdb->prepare($sql, $thread_id, $client_id);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_last_thread_of_client($client_id){

     $client_id = esc_sql($client_id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_thread 
               where client_id = '%s'
          order by init desc
          limit 1
EOD;
     $sql = $wpdb->prepare($sql, $client_id);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_last_record_of_client($client_id, $thread_id){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_rec where client_id = '%s'
               and thread_id = '%s'
          order by init desc
          limit 1
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

}



function bb_get_assets_by_field_ref($client_id, $thread_id, $field){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $field_ref = esc_sql($field->ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_asset where client_id = '%s'
               and thread_id = '%s'
               and field_ref = '%s'
          order by init desc
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $field_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_assets_by_group_ref($client_id, $thread_id, $field){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $group_ref = esc_sql($field->group_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_asset where client_id = '{$client_id}'
               and thread_id = '{$thread_id}'
               and group_ref = '{$group_ref}'
          order by init desc
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $group_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_group_by_ref($group_ref){

     $group_ref = esc_sql($group_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_group where ref = '%s'
EOD;
     $sql = $wpdb->prepare($sql, $group_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_ticket_of_client($client_id){

     $client_id = esc_sql($client_id);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_ticket where client_id = '%s'
               order by init desc
               limit 1
EOD;
     $sql = $wpdb->prepare($sql, $client_id);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_set_ticket_of_client($client_id, $thread_id, $field_ref, $rec_pos, $view_state){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $field_ref = esc_sql($field_ref);
     $rec_pos = esc_sql($rec_pos);
     $view_state = esc_sql($view_state);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_ticket
               (client_id, thread_id, field_ref, rec_pos, view_state, init)
          values 
               (
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    now()
               )
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $field_ref, $rec_pos, $view_state);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;

}



function bb_insert_spread($client_id, $thread_id, $field, $doc){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);

     $survey_ref = esc_sql($field->survey_ref);
     $group_ref = esc_sql($field->group_ref);
     $field_ref = esc_sql($field->ref);

     $doc = esc_sql($doc);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_spread
               (
               client_id, thread_id, survey_ref, group_ref, field_ref, 
               doc, init
               )
          values 
               (
               '%s', '%s', '%s', '%s', '%s', 
               '%s', now()
               )
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $survey_ref, $group_ref, $field_ref, $doc);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_get_spreads_of_client_by_field_ref($client_id, $thread_id, $field_ref){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_spread 
               where client_id = '%s' 
               and thread_id = '%s' 
               and field_ref = '%s'
               order by init desc
               limit 1
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $field_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_assetcount_of_field($client_id, $thread_id, $field_ref){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $field_ref = esc_sql($field_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select count(id) as max from {$prefix}ts_bb_asset 
               where client_id = '%s' 
               and thread_id = '%s'
               and field_ref = '%s'
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $field_ref);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_get_conf(){

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_conf 
EOD;
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_init_conf(){

     $root_survey_title = esc_sql(Proc::KICKOFF_SURVEY_TITLE);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_conf
               (root_survey_title, init)
          values 
               (
                    '%s',
                    now()
               )
EOD;
     $sql = $wpdb->prepare($sql, $root_survey_title);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}



function bb_get_hidden_field_of_client_by_title($client_id, $thread_id, $title){

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);
     $title = esc_sql($title);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_hidden 
               where client_id = '%s'
               and thread_id = '%s'
               and title = '%s'
               order by init desc
               limit 1
EOD;
     $sql = $wpdb->prepare($sql, $client_id, $thread_id, $title);
     $sql = bb_debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}



function bb_insert_hidden_fields($client_id, $thread_id, $fields){

     if(is_null($fields)){
          return false;
     }

     $client_id = esc_sql($client_id);
     $thread_id = esc_sql($thread_id);

     global $wpdb;
     $prefix = $wpdb->prefix;

     $res = true;
     foreach($fields as $field){

          $title = esc_sql($field['key']);
          $doc = esc_sql($field['val']);

          $sql = <<<EOD
               insert into {$prefix}ts_bb_hidden
                    (client_id, thread_id, title, doc, init)
               values 
                    ('{$client_id}', '{$thread_id}', '{$title}', '{$doc}', now())
               on duplicate key update 
                    client_id = '{$client_id}', 
                    thread_id = '{$thread_id}', 
                    title = '{$title}',
                    doc = '{$doc}',
                    init = now()
EOD;
/*
          $sql = <<<EOD
               insert into {$prefix}ts_bb_hidden
                    (client_id, thread_id, title, doc, init)
               values 
                    ('%s', '%s', '%s', '%s', now())
               on duplicate key update 
                    client_id = '%s', 
                    thread_id = '%s', 
                    title = '%s',
                    doc = '%s',
                    init = now()
EOD;
*/
          $sql = $wpdb->prepare($sql, $client_id, $thread_id, $title, $doc);
          $sql = bb_debug_sql($sql);
          $ins = $wpdb->query($sql);
          $res = false == $res ? false : $ins;
     }
     return $res;
}



