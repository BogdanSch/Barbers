<?php

namespace SLB_API_Mobile\Controller;

use SLN_Plugin;
use WP_REST_Server;
use SLN_DateTime;
use Salon\Util\Date;
use SLN_Enum_BookingStatus;
use DateTime;
use DateInterval;

class Calendar_Controller extends REST_Controller
{
    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'calendar';

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/intervals', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_intervals'),
		'permission_callback' => '__return_true',
                'args'     => apply_filters('sln_api_calendar_register_routes_get_intervals_args', array()),
            ),
        ) );
    }

    public function get_intervals( $request )
    {
        try {

            do_action('sln_api_calendar_get_intervals_before', $request);

            do_action('sln_api_calendar_get_intervals_before_check', $request);

            $plugin   = SLN_Plugin::getInstance();

            $ai = $plugin->getSettings()->getAvailabilityItems();

            list($timestart, $timeend) = $ai->getTimeMinMax();

            $interval = $plugin->getSettings()->getInterval();

            $ret = array();

            $start = explode(':', $timestart);
            $end   = explode(':', $timeend);

            $curr = (new SLN_DateTime())->setTime($start[0],$start[1]);

            $end = (new SLN_DateTime())->setTime($end[0],$end[1]);

            do {
                $ret[] = $curr->format("H:i");
                $curr = $curr->add(new DateInterval('PT'.((int)$interval*60).'S'));
            } while ($curr <= $end);

            return $this->success_response(array('items' => $ret));

        } catch (\Exception $ex) {
            return new \WP_Error( 'salon_rest_cannot_view', $ex->getMessage(), array( 'status' => $ex->getCode() ? $ex->getCode() : 500 ) );
        }
    }

}