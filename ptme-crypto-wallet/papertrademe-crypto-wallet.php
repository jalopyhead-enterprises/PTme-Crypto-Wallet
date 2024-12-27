<?php
/*
Plugin Name: PTMe Crypto Wallet
Plugin URI: https://papertrademe.com
Description: A WordPress plugin for managing and displaying cryptocurrency data.
Version: 1.0
Author: Your Name
Author URI: https://jalopyhead.com
License: GPL2
*/

// Define Plugin Constants
if (!defined('PAPERTRADEME_PLUGIN_DIR')) {
    define('PAPERTRADEME_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('PAPERTRADEME_PLUGIN_URL')) {
    define('PAPERTRADEME_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Auto-load all shortcodes from the includes/shortcodes directory
foreach (glob(PAPERTRADEME_PLUGIN_DIR . 'includes/shortcodes/*.php') as $file) {
    require_once $file;
}

// Optionally: Load other components like classes or AJAX handlers
require_once PAPERTRADEME_PLUGIN_DIR . 'includes/class-coinbridge.php';
require_once PAPERTRADEME_PLUGIN_DIR . 'includes/ajax-handlers.php';

// Auto-Enqueue JS Files
function papertrademe_enqueue_all_js_files() {
    $js_dir = PAPERTRADEME_PLUGIN_DIR . 'assets/js/';
    $js_url = PAPERTRADEME_PLUGIN_URL . 'assets/js/';

    // Automatically enqueue each JS file in the directory
    foreach (glob($js_dir . '*.js') as $file_path) {
        $file_name = basename($file_path);
        $handle = 'ptme-' . str_replace('.js', '', $file_name); 

        wp_enqueue_script(
            $handle, 
            $js_url . $file_name, 
            ['jquery'], 
            filemtime($file_path), // Use file modification time for versioning
            true
        );
    }

    // Localize shared data for AJAX
    wp_localize_script('ptme-main', 'paperTradeMeData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('papertrademe_nonce'),
    ]);
}

// Enqueue Styles and Scripts
function papertrademe_enqueue_assets() {
    $css_file = PAPERTRADEME_PLUGIN_DIR . 'assets/css/styles.css';

    // Enqueue Styles with Timestamp Versioning
    wp_enqueue_style(
        'papertrademe-styles',
        PAPERTRADEME_PLUGIN_URL . 'assets/css/styles.css',
        [],
        filemtime($css_file)
    );

    // Enqueue All JS Files Automatically
    papertrademe_enqueue_all_js_files();
}

add_action('wp_enqueue_scripts', 'papertrademe_enqueue_assets');

// Activation Hook
function papertrademe_activate() {
    // Activation tasks
}
register_activation_hook(__FILE__, 'papertrademe_activate');

// Deactivation Hook
function papertrademe_deactivate() {
    // Cleanup tasks
}
register_deactivation_hook(__FILE__, 'papertrademe_deactivate');
