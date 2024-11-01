<?php

/**
 * Plugin Name:       WDS Themes Manager
 * Plugin URI:        http://www.wordpress.org/wds-themes-manager
 * Description:       WDS Themes Manager is developed for the convenience of changing the theme view while using different devices, os, time and many other.
 * Version:           1.0.0
 * Author:            Web Design Sun Team
 * Author URI:        http://www.webdesignsun.com
 * Text Domain:       wdstm
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

if ( ! defined( 'WDSTM_PLUGIN_DIR' ) )
	define( 'WDSTM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ )) );

if ( ! defined( 'WDSTM_DB_VERSION' ) )
	define( 'WDSTM_DB_VERSION', '1.0' );

if ( ! defined( 'WDSTM_MEDIA_DIR' ) )
    define('WDSTM_MEDIA_DIR', plugins_url( '/assets' , __FILE__ ));

if ( ! defined( 'WDSTM_SCREENSHOT_DIR' ) )
	define('WDSTM_SCREENSHOT_DIR', plugins_url( '/' , __FILE__ ));

if ( ! defined( 'WDSTM_VENDOR_DIR' ) )
    define( 'WDSTM_VENDOR_DIR', WDSTM_PLUGIN_DIR . '/includes/vendor' );

function wdstm_initialize() {
	add_option( "wdstm_sign_db_version", WDSTM_DB_VERSION );
	add_option( "wdstm-activate-plugin", '' );
	add_option( "wdstm_default_theme", '' );
    add_option('wdstm_order_filter', '');
}

register_activation_hook(__FILE__, 'wdstm_initialize');
register_uninstall_hook(__FILE__, 'wdstm_rollback');

/**
 * get table name
 */
function wdstm_sign_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'wdstm_base';
}
$table_name = wdstm_sign_get_table_name();

/**
 * unregister all options,
 * delete plugin database
 */
function wdstm_rollback() {
	global $wpdb, $table_name;
	wp_deregister_style('wdstm-style');
	wp_deregister_script('wdstm-script');
	delete_option('wdstm_sign_db_version');
	delete_option('wdstm-activate-plugin');
	delete_option('wdstm_default_theme');
    delete_option('wdstm_order_filter');

	$sql = "DROP TABLE $table_name";
	$wpdb->query($sql);
}

/**
 * settins page
 */
function wdstm_admin_page() {
	add_menu_page('WDS Themes Manager', 'WDS Themes Manager', 'manage_options', __FILE__ . '/includes', 'wdstm_options_page', 'dashicons-smiley' , 25);
}
add_action('admin_menu', 'wdstm_admin_page');

/**
 * js scripts
 */
add_action( 'admin_enqueue_scripts', 'wdstm_admin_site_scripts' );
function wdstm_admin_site_scripts() {
	$get_template_directory_uri = untrailingslashit( plugins_url( '', __FILE__ ) );

//	jquery scripts from wordpress core
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-draggable');


    wp_enqueue_script('sweetalert_js', $get_template_directory_uri . '/assets/js/sweetalert.min.js', array('jquery'), '1.0.0');
    wp_enqueue_script('jquery_fancybox_js', $get_template_directory_uri . '/assets/js/jquery.fancybox.min.js', array('jquery'), '3.2.10');
	wp_enqueue_script('wdstm_admin_js', $get_template_directory_uri . '/assets/js/wdstm-admin.js', array('jquery'), '1.0.0');
	wp_enqueue_script('timepicker_js', $get_template_directory_uri . '/assets/js/timepicker.min.js', array('jquery'), '1.11.11');
	wp_enqueue_script('wdstm_jquery_punch_js', $get_template_directory_uri . '/assets/js/jquery.punch.min.js', array('jquery'), '0.2.3');
	wp_localize_script('wdstm_admin_js', 'wdstmajax', array('ajaxurl' => admin_url('admin-ajax.php')));
	wp_enqueue_style('wdstm_jquery_ui_css', $get_template_directory_uri . '/assets/css/jquery-ui.min.css');
	wp_enqueue_style('wdstm_front_css', $get_template_directory_uri . '/assets/css/wdstm-admin.css');
	wp_enqueue_style('timepicker-ui', $get_template_directory_uri . '/assets/css/timepicker.css');
	wp_enqueue_style('sweetalert-css', $get_template_directory_uri . '/assets/css/sweetalert.min.css');
	wp_enqueue_style('fancybox-css', $get_template_directory_uri . '/assets/css/jquery.fancybox.min.css');
	wp_enqueue_style('fontello', $get_template_directory_uri . '/assets/css/fontello.css');
}

/**
 * load translate
 */
add_action( 'plugins_loaded', 'wdstm_load_plugin_textdomain' );
function wdstm_load_plugin_textdomain() {
	load_plugin_textdomain( 'wdstm', false, WDSTM_PLUGIN_DIR . '/languages' );
}

require_once WDSTM_VENDOR_DIR . '/autoload.php';

require_once WDSTM_PLUGIN_DIR . '/includes/wdstm_db.php';
require_once WDSTM_PLUGIN_DIR . '/includes/wdstm_functions.php';
require_once WDSTM_PLUGIN_DIR . '/includes/wdstm_ajax.php';
require_once WDSTM_PLUGIN_DIR . '/includes/wdstm_datepicker.php';
require_once WDSTM_PLUGIN_DIR . '/includes/wdstm_options_page.php';
require_once WDSTM_PLUGIN_DIR . '/includes/wdstm_arrays.php';



wdstm_create_db_table();

add_action( 'plugins_loaded', 'wdstm_change_theme' );


function wdstm_change_theme() {

    $default_theme = get_option('wdstm_default_theme');

    if(get_option('wdstm-activate-plugin') != '' && $default_theme != '') {        
        add_filter('stylesheet', 'wdstm_get_theme_stylesheet');
		add_filter('template', 'wdstm_get_theme_template');        
    }
}




