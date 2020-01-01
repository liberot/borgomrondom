<?php

/**
 * Plugin Name: Book Builder
 * Plugin URI: http://127.0.0.1:8083
 * Description: Book Builder Plugin as for to build Print Spreads from generic Quesionnaire Resultsets
 * Version: 0.0.0.0.1
 * Author: Book Builder
 *
 * @package bookbuilder_survey
 */

defined('ABSPATH') || exit;

require_once('survey/index.php');


register_activation_hook(__FILE__, 'on_plugin_activation');
register_deactivation_hook(__FILE__, 'on_plugin_deactivation');
function on_plugin_activation(){
     insert_guest_client();
     init_survey_page();
}
function on_plugin_deactivation(){
}

