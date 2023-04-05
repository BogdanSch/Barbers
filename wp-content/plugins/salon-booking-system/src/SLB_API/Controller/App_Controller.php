<?php

namespace SLB_API\Controller;

use SLN_Plugin;
use WP_REST_Server;

class App_Controller extends REST_Controller
{
    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'app';

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/about', array(
            array(
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => array( $this, 'get_about_info' ),
		'permission_callback' => '__return_true',
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/settings', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_settings' ),
		'permission_callback' => '__return_true',
	    ),
	) );
    }

    public function get_about_info() {

	$info = array(
	    'name'        => $this->get_app_name(),
	    'version'	  => $this->get_app_version(),
	    'pro_version' => $this->get_app_pro_version(),
	    'author'	  => $this->get_app_author(),
	);

	return $this->success_response(array('info' => $info));
    }

    protected function get_app_name() {
	return defined('SLN_ITEM_NAME') ? SLN_ITEM_NAME : '';
    }

    protected function get_app_version() {
	return defined('SLN_VERSION') ? SLN_VERSION : '';
    }

    protected function get_app_pro_version() {
	return defined('SLN_VERSION_PAY') || defined('SLN_VERSION_CODECANYON');
    }

    protected function get_app_author() {
	return defined('SLN_AUTHOR') ? SLN_AUTHOR : '';
    }

    public function get_settings()
    {
	$plugin	= SLN_Plugin::getInstance();
	$s	= $plugin->getSettings();

	$date_format = $s->get('date_format');
	$time_format = $s->get('time_format');

	$settings = array(
	    'attendant_enabled' => (bool)$s->get('attendant_enabled'),
	    'date_format'	=> array(
		'type'	     => $date_format,
		'php_format' => \SLN_Enum_DateFormat::getPhpFormat($date_format),
		'js_format'  => \SLN_Enum_DateFormat::getJsFormat($date_format),
	    ),
	    'time_format'	=> array(
		'type'	     => $time_format,
		'php_format' => \SLN_Enum_TimeFormat::getPhpFormat($time_format),
		'js_format'  => \SLN_Enum_TimeFormat::getJsFormat($time_format),
	    ),
	);

	return $this->success_response(array('settings' => $settings));
    }

}