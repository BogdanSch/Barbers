<?php

/*
Plugin Name: Salon Booking Wordpress Plugin - Free Version
Description: Let your customers book you services through your website. Perfect for hairdressing salons, barber shops and beauty centers.
Version: 8.4
Plugin URI: http://salonbookingsystem.com/
Author: Salon Booking System
Author URI: http://salonbookingsystem.com/
Text Domain: salon-booking-system
Domain Path: /languages
 */

if( !function_exists( 'sln_deactivate_plugin' ) ){
	function sln_deactivate_plugin(){
		if( function_exists( 'sln_autoload' ) ){  //deactivate for other version
			spl_autoload_unregister( 'sln_autoload' );
		}
		if( function_exists( 'my_update_notice' ) ){
			remove_action( 'in_plugin_update_message-' . SLN_PLUGIN_BASENAME, 'my_update_notice' );
		}

		global $sln_autoload, $my_update_notice; //deactivate for this version
		if( isset( $sln_autoload ) ){
			spl_autoload_unregister( $sln_autoload );
		}
		if( isset( $my_update_notice ) ){
			remove_action( 'in_plugin_update_message-' . SLN_PLUGIN_BASENAME, $my_update_notice);
		}
		deactivate_plugins( SLN_PLUGIN_BASENAME );
	}
}

if ( defined( 'SLN_PLUGIN_BASENAME' ) ) {
    if ( ! function_exists( 'deactivate_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    sln_deactivate_plugin();
}

define('SLN_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SLN_PLUGIN_DIR', untrailingslashit(dirname(__FILE__)));
define('SLN_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));
define('SLN_VERSION', '8.4');
define('SLN_STORE_URL', 'https://salonbookingsystem.com');
define('SLN_AUTHOR', 'Salon Booking');
define('SLN_UPLOADS_DIR', wp_upload_dir()['basedir'] . '/sln_uploads/');
define('SLN_UPLOADS_URL', wp_upload_dir()['baseurl'] . '/sln_uploads/');
define('SLN_ITEM_SLUG', 'salon-booking-wordpress-plugin');
define('SLN_ITEM_NAME', 'Salon booking wordpress plugin');
define('SLN_API_KEY', '0b47c255778d646aaa89b6f40859b159');
define('SLN_API_TOKEN', '7c901a98fa10dd3af65b038d6f5f190c');



define('SLN_ONESIGNAL_USER_AUTH_KEY', 'YTc3MDkyMjYtMGZiMC00OGI1LTliMDAtZjA2NTZhMGRmZDNl');

$sln_autoload = function($className) {
	if (strpos($className, 'SLN_') === 0) {
		$filename = SLN_PLUGIN_DIR . "/src/" . str_replace("_", "/", $className) . '.php';
		if (file_exists($filename)) {
			require_once $filename;
			return;
		}
	} elseif (strpos($className, 'Salon') === 0) {
		$filename = SLN_PLUGIN_DIR . "/src/" . str_replace("\\", "/", $className) . '.php';
		if (file_exists($filename)) {
			require_once $filename;
			return;
		}
	}

	$discountAppPrefixes = array(
		'SLB_Discount_',
		'SLN_',
	);
	foreach ($discountAppPrefixes as $prefix) {
		if (strpos($className, $prefix) === 0) {
			$classWithoutPrefix = str_replace("_", "/", substr($className, strlen($prefix)));
			$filename = SLN_PLUGIN_DIR . "/src/" . substr($prefix, 0, -1) . "/{$classWithoutPrefix}.php";
			if (file_exists($filename)) {
				require_once $filename;
				return;
			}
		}
	}

	if (strpos($className, 'SLB_API') === 0) {
		$filename = SLN_PLUGIN_DIR . "/src/" . str_replace("\\", "/", $className) . '.php';
		if (file_exists($filename)) {
			require_once $filename;
			return;
		}
	}

	if (strpos($className, 'SLB_Customization') === 0) {
		$filename = SLN_PLUGIN_DIR . "/src/" . str_replace("\\", "/", $className) . '.php';
		if (file_exists($filename)) {
			require_once $filename;
			return;
		}
	}

	if (strpos($className, 'SLB_Zapier') === 0) {
		$filename = SLN_PLUGIN_DIR . "/src/" . str_replace("\\", "/", $className) . '.php';
		if (file_exists($filename)) {
			require_once $filename;
			return;
		}
	}
	if (strpos($className, 'SLB_PWA') === 0) {
		$filename = SLN_PLUGIN_DIR . "/src/" . str_replace("\\", "/", $className) . '.php';
		if (file_exists($filename)) {
			require_once $filename;
			return;
		}
	}
};

$my_update_notice = function() {
	$info = __('-', 'salon-booking-system');
	echo '<span class="spam">' . strip_tags($info, '<br><a><b><i><span>') . '</span>';
};

if (is_admin()) {
	add_action('in_plugin_update_message-' . plugin_basename(__FILE__), $my_update_notice);
}

add_action("in_plugin_update_message-" . plugin_basename(__FILE__), function ($plugin_data, $response) {
	echo '<span style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px; display: block"><strong>' . __('Attention: this is a major release, please make sure to clear your browser cache after the plugin update.', 'salon-booking-system') . '</strong></span>';
}, 10, 2);

add_action('plugins_loaded', function () {
	add_filter('plugin_locale', function ($locale, $domain) {
		if ($domain === 'salon-booking-system') {
			return SLN_Plugin::getInstance()->getSettings()->getDateLocale();
		}
		return $locale;
	}, 10, 2);
	load_plugin_textdomain('salon-booking-system', false, dirname(SLN_PLUGIN_BASENAME) . '/languages');
});

spl_autoload_register($sln_autoload);
$sln_plugin = SLN_Plugin::getInstance();
do_action('sln.init', $sln_plugin);

add_action('init', function () {
	if ((!session_id() || session_status() !== PHP_SESSION_ACTIVE)
		&& !strstr($_SERVER['REQUEST_URI'], '/wp-admin/site-health.php')
		&& !strstr($_SERVER['REQUEST_URI'], '/wp-json/wp-site-health')
		&& !(isset($_POST['action']) && $_POST['action'] === 'health-check-loopback-requests')
		&& !(isset($_REQUEST['action']) && $_REQUEST['action'] === 'wp_async_send_server_events')
	) {
		session_start();
	}
});

add_action('init', function () {

	if (!empty($_GET['action']) && $_GET['action'] === 'updraftmethod-googledrive-auth') {
		return;
	}

	//TODO[feature-gcalendar]: move this require in the right place
	require_once SLN_PLUGIN_DIR . "/src/SLN/Third/GoogleScope.php";
	$sln_googlescope = new SLN_GoogleScope();
	$GLOBALS['sln_googlescope'] = $sln_googlescope;
	$sln_googlescope->set_settings_by_plugin(SLN_Plugin::getInstance());
	$sln_googlescope->wp_init();
	SLN_Third_GoogleCalendarImport::launch($GLOBALS['sln_googlescope']);
});

$sln_api = \SLB_API\Plugin::get_instance();
$sln_api_mobile = \SLB_API_Mobile\Plugin::get_instance();

$sln_customization = \SLB_Customization\Plugin::get_instance();

$sln_zapier = \SLB_Zapier\Plugin::get_instance();

$sln_pwa = \SLB_PWA\Plugin::get_instance();

add_filter('body_class', function ($classes) {
	return array_merge($classes, array('sln-salon-page'));
});

ob_start();
