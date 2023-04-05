<?php

namespace SLB_API_Mobile\Controller;

use WP_REST_Server;
use SLN_Func;

class Timeslots_Controller extends REST_Controller
{
    protected $rest_base = 'timeslots';

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => '__return_true',
            ),
        ) );
    }

    public function get_items( $request )
    {
        return $this->success_response(array('items' => SLN_Func::getMinutesIntervals()));
    }
}