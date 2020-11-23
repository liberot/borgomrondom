<?php defined('ABSPATH') || exit;

class Role {
     const GUEST = 'surveyprint_guest';
     const CUSTOMER = 'surveyprint_customer';
     const SUBSCRIBER = 'subscriber';
     const ADMIN = 'administrator';
}

class Path {
     const INKSCAPE = '/Applications/Inkscape.app/Contents/MacOS/inkscape';
     const GHOSTSCRIPT = '/opt/local/bin/gs';
     const SERVICE_BASE = '/wp-admin/admin.php';
}

class Layout {
     const ASSUMED_SVG_UNIT = 72;
     const DESIRED_PPI = 300;
     const IMAGE_MAX_SCALE_RATIO = 1.3;
     const IMAGE_SCALE_TYPE = 'cut_into_slot';
     const CUT_INTO_SLOT = 'cut_into_slot';
     const NO_SCALE = 'no_scale';
}

class Server {
     const PRE_GENERATE_SECTION_PANELS = false;
     const UPDATE_ON_PERSIST = true;
}


