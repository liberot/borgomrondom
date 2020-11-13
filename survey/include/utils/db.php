<?php defined('ABSPATH') || exit;

// add_action('init', 'init_ref_table');
function init_ref_table(){
     $sql = <<<EOD
     create table if not exists
          wp_xurveyprint_ref (
               post_id bigint(20) unsigned not null,
               post_parent_id bigint(20) unsigned not null,
               title varchar(255) null,
               name varchar(255) null,
               note varchar(255) null,
               description varchar(255),
               content varchar(255),
               init datetime,
               foreign key (post_id) references wp_posts(ID),
               foreign key (post_parent_id) references wp_posts(ID),
               primary key (post_id, post_parent_id)
          )
          engine=innodb
EOD;
     $sql = debug_sql($sql);
     // insert into wp_xurveyprint_ref (post_id, post_parent_id, init) values (31453, 31450, now());
     global $wpdb;
     $res = $wpdb->query($sql);
}



