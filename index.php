<?php

/**
 * Plugin Name: BookBuilder
 * Plugin URI: http://127.0.0.1:8083
 * Description: BookBuilder Plugin as for to build Print Spreads from generic Questionnaire Resultsets
 * Version: 0.0.0.0.1
 * Author: BookBuilder
 *
 * @package bookbuilder_survey
 */

defined('ABSPATH') || exit;

require_once('survey/index.php');

register_activation_hook(__FILE__, 'on_plugin_activation');
function on_plugin_activation(){
     insert_survey_page();
     insert_guest_client();
}

register_deactivation_hook(__FILE__, 'on_plugin_deactivation');
function on_plugin_deactivation(){

}

