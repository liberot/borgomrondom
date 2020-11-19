<?php defined('ABSPATH') || exit;

class Role {
     const GUEST = 'surveyprint_guest';
     const CUSTOMER = 'surveyprint_customer';
     const ADMIN = 'administrator';
}

class Path {
     const INKSCAPE = '/Applications/Inkscape.app/Contents/MacOS/inkscape';
     const GHOSTSCRIPT = '/opt/local/bin/gs';
}

class Layout {
     const ASSUMED_SVG_UNIT = 96;
     const IMAGE_SCALE_TYPE = 'cut_into_slot';
     const CUT_INTO_SLOT = 'cut_into_slot';
     const NO_SCALE = 'no_scale';
}

