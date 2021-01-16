<?php defined('ABSPATH') || exit;

class Role {
     const         GUEST = 'surveyprint_guest';
     const      CUSTOMER = 'surveyprint_customer';
     const    SUBSCRIBER = 'subscriber';
     const         ADMIN = 'administrator';
}

class Path {

     const      INKSCAPE  = '/Applications/Inkscape.app/Contents/MacOS/inkscape';
     const   GHOSTSCRIPT  = '/opt/local/bin/gs';
     const  SERVICE_BASE  = '/wp-admin/admin.php';

     public static final function get_upload_path(){
          return sprintf('%s%s', wp_upload_dir()['basedir'], '/book_builder');
     }

     public static final function get_upload_url(){
          return sprintf('%s%s', wp_upload_dir()['baseurl'], '/book_builder');
     }

     public static final function get_backup_dir(){
          $base = Path::get_base_dir();
          return sprintf('%s%s', $base, '/asset/backup');
     }

     public static final function get_asset_dir(){
          $base = Path::get_base_dir();
          return sprintf('%s%s', $base, '/asset');
     }

     public static final function get_typeform_dir(){
          $base = Path::get_base_dir();
          return sprintf('%s%s', $base, '/asset/typeform');
     }

     public static final function get_layout_template_dir(){
          $base = Path::get_base_dir();
          return sprintf('%s%s', $base, '/asset/default-layouts/svg');
     }

     public static final function get_mock_dir(){
          $base = Path::get_base_dir();
          return sprintf('%s%s', $base, '/asset/mock');
     }

     public static final function get_plugin_dir(){
          return get_base_dir();
     }

     private static final function get_base_dir(){
          $base = plugin_dir_path(__DIR__);
          $base = preg_match('/^(.{0,1024})\/bookbuilder\/survey\//', $base, $mtch);
          $base = $mtch[1];
          return sprintf('%s%s', $base, '/bookbuilder/survey');
     }
}

class Layout {
// --------------------------------------------------------
     const            ASSUMED_SVG_UNIT = 72;
     const                 DESIRED_PPI = 300;
// --------------------------------------------------------
     const             IMAGE_MAX_SCALE = 1.25;
// --------------------------------------------------------
     const                    NO_SCALE = 'no_scale';
     const               CUT_INTO_SLOT = 'cut_into_slot';
     const            IMAGE_SCALE_TYPE = 'cut_into_slot';
// --------------------------------------------------------
     const                      Y_STEP = 13;
}

class Proc {
     const        UPDATE_ON_PERSIST = false;
     const EVAL_UPLOADED_ASSET_SIZE = true;
     const        MEDIA_UPLOAD_PROC = 'base64_upload';
     const            BASE64_UPLOAD = 'base64_upload';
     const              FILE_UPLOAD = 'file_upload';
     const     KICKOFF_SURVEY_TITLE = 'BBC0 Cover and Preface';
//   const     KICKOFF_SURVEY_TITLE = '201204 Cover and Preface';
}


