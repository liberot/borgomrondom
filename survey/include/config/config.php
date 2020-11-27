<?php defined('ABSPATH') || exit;

class Role {
     const                       GUEST = 'surveyprint_guest';
     const                    CUSTOMER = 'surveyprint_customer';
     const                  SUBSCRIBER = 'subscriber';
     const                       ADMIN = 'administrator';
}

class Path {
     const                    INKSCAPE = '/Applications/Inkscape.app/Contents/MacOS/inkscape';
     const                 GHOSTSCRIPT = '/opt/local/bin/gs';
     const                SERVICE_BASE = '/wp-admin/admin.php';
     const            UPLOAD_DIRECTORY = '/wp-content/uploads/book_builder';

     static public final function get_upload_path(){
          return sprintf('%s%s', wp_upload_dir()['basedir'], '/book_builder');
     }

     static public final function get_upload_url(){
          return sprintf('%s%s', wp_upload_dir()['baseurl'], '/book_builder');
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
     const                      Y_STEP = 101;
}

class Proc {
     const PRE_GENERATE_SECTION_PANELS = false;
     const           UPDATE_ON_PERSIST = false;
     const    EVAL_UPLOADED_ASSET_SIZE = true;
     const           MEDIA_UPLOAD_PROC = 'base64_upload';
     const               BASE64_UPLOAD = 'base64_upload';
     const                 FILE_UPLOAD = 'file_upload';
}


