<?php

namespace SLB_API_Mobile\Controller;

use WP_REST_Server;

class Account_Controller extends REST_Controller
{
    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'account';

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => array( $this, 'get_info' ),
		'permission_callback' => '__return_true',
            ),
        ) );
    }

    public function get_info() {

        $current_user = wp_get_current_user();

	$info = array(
	    'login'        => $current_user->user_login,
	    'email'	   => $current_user->user_email,
	    'display_name' => $current_user->display_name,
	);

	return $this->success_response(array('info' => $info));
    }

}