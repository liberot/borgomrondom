<?php defined('ABSPATH') || exit;

class Role {
     const            GUEST = 'surveyprint_guest';
     const         CUSTOMER = 'surveyprint_customer';
     const       SUBSCRIBER = 'subscriber';
     const            ADMIN = 'administrator';
}

class Path {
     const         INKSCAPE = '/Applications/Inkscape.app/Contents/MacOS/inkscape';
     const      GHOSTSCRIPT = '/opt/local/bin/gs';
     const     SERVICE_BASE = '/wp-admin/admin.php';
}

class Layout {
     const ASSUMED_SVG_UNIT = 72;
     const      DESIRED_PPI = 300;
// --------------------------------------------------------
     const  IMAGE_MAX_SCALE = 1.25;
// --------------------------------------------------------
     const         NO_SCALE = 'no_scale';
     const    CUT_INTO_SLOT = 'cut_into_slot';
     const IMAGE_SCALE_TYPE = 'cut_into_slot';
// --------------------------------------------------------
     const Y_STEP = 101;
}

class Server {
     const PRE_GENERATE_SECTION_PANELS = false;
     const           UPDATE_ON_PERSIST = true;
}


