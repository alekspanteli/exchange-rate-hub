<?php
/*
Plugin Name: Exchange Rate Hub
Description: Centralized, maintainable, and extensible exchange rate management for WordPress.
Version: 1.0.0
Author: Your Name
License: GPL2
*/

if (!defined('ABSPATH')) exit;

// Plugin constants
define('ERH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ERH_PLUGIN_URL', plugin_dir_url(__FILE__));

global $erh_db_version;
$erh_db_version = '1.0';

// Activation/deactivation hooks
register_activation_hook(__FILE__, 'erh_activate_plugin');
register_deactivation_hook(__FILE__, 'erh_deactivate_plugin');

function erh_activate_plugin() {
    require_once(ERH_PLUGIN_DIR . 'includes/class-erh-activator.php');
    ERH_Activator::activate();
}

function erh_deactivate_plugin() {
    // Clean up scheduled events, etc.
    require_once(ERH_PLUGIN_DIR . 'includes/class-erh-deactivator.php');
    ERH_Deactivator::deactivate();
}

// Core loader
require_once(ERH_PLUGIN_DIR . 'includes/class-erh-loader.php');
ERH_Loader::init();
