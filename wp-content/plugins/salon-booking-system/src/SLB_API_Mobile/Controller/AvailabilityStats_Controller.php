<?php

namespace SLB_API_Mobile\Controller;

use SLN_Plugin;
use WP_REST_Server;
use SLN_DateTime;
use Salon\Util\Date;
use SLN_Enum_BookingStatus;

class AvailabilityStats_Controller extends REST_Controller
{
    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'availability/stats';

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            'args' => apply_filters('sln_api_availability_stats_register_routes_get_stats_args', array(
                'from_date'     => array(
                    'description'       => __('From date.', 'salon-booking-system'),
                    'type'              => 'string',
                    'format'            => 'YYYY-MM-DD',
                    'required'          => true,
                    'validate_callback' => array($this, 'rest_validate_request_arg'),
                ),
                'to_date'     => array(
                    'description'       => __('To date.', 'salon-booking-system'),
                    'type'              => 'string',
                    'format'            => 'YYYY-MM-DD',
                    'required'          => true,
                    'validate_callback' => array($this, 'rest_validate_request_arg'),
                ),
            )),
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_stats'),
		'permission_callback' => '__return_true',
            ),
        ) );
    }

    public function get_stats( $request )
    {
        try {

            do_action('sln_api_availability_stats_get_availability_stats_before', $request);

            $from = (new SLN_DateTime)->setTimestamp( strtotime( sanitize_text_field( wp_unslash( $request->get_param('from_date') ) ) ) );

            $to = (new SLN_DateTime)->setTimestamp( strtotime( sanitize_text_field( wp_unslash( $request->get_param('to_date') ) ) ) );

            do_action('sln_api_availability_stats_get_availability_stats_before_check', $request);

            $plugin   = SLN_Plugin::getInstance();
            $bc	  = $plugin->getBookingCache();
            $bookings = $this->getBookings($from, $to);
            $clone	  = clone $from;
            $ret	  = array();

            while ($clone <= $to) {
                $dd = clone $clone;
                $dd->modify('+1 hour');
                $dd = new Date($dd);

                $tmp = array('date' => $dd->toString('Y-m-d'), 'available' => true);

                $bc->processDate($dd);
                $cache = $bc->getDay($dd);
                if ($cache && $cache['status'] == 'booking_rules') {
                    $tmp['error']		 = array();
                    $tmp['available']	 = false;
                    $tmp['error']['type']	 = $cache['status'];
                    $tmp['error']['message'] = __('Booking Rule', 'salon-booking-system');
                } elseif ($cache && $cache['status'] == 'holiday_rules') {
                    $tmp['error']		 = array();
                    $tmp['available']	 = false;
                    $tmp['error']['type']	 = $cache['status'];
                    $tmp['error']['message'] = __('Holiday Rule', 'salon-booking-system');
                } else {
                    $tot = 0;
                    $cnt = 0;
                    foreach ($bookings as $b) {
                        if ($b->getDate()->format('Ymd') == $clone->format('Ymd')) {
                            if (!$b->hasStatus(
                                array(
                                    SLN_Enum_BookingStatus::CANCELED,
                                )
                            )
                            ) {
                                $tot += $b->getAmount();
                                $cnt++;
                            }
                        }
                    }
                    if (isset($cache['free_slots'])) {
                        $free = count($cache['free_slots']) * $plugin->getSettings()->getInterval();
                    } else {
                        $free = 0;
                    }

                    $tmp['full_booked'] = false;

                    if ($cache && $cache['status'] == 'full') {
                        $tmp['full_booked'] = true;
                    }

                    $freeH = intval($free / 60);
                    $freeM = ($free % 60);

                    $tmp['data'] = array(
                        'bookings' => $cnt,
                        'revenue'  => $tot,
                        'currency' => $plugin->getSettings()->getCurrencySymbol(),
                        'available_left' => array(
                            'hours' => $freeH,
                            'mins'  => $freeM > 0 ? $freeM : 0,
                        )
                    );

                }
                $ret[] = $tmp;
                $clone->modify('+1 days');
            }

            return $this->success_response(array('stats' => $ret));

        } catch (\Exception $ex) {
            return new \WP_Error( 'salon_rest_cannot_view', $ex->getMessage(), array( 'status' => $ex->getCode() ? $ex->getCode() : 500 ) );
        }
    }

    private function getBookings($from, $to)
    {
        return SLN_Plugin::getInstance()
            ->getRepository(SLN_Plugin::POST_TYPE_BOOKING)
            ->get($this->getCriteria($from, $to));
    }

    private function getCriteria($from, $to)
    {
        $criteria = array();
        if ($from->format('Y-m-d') == $to->format('Y-m-d')) {
            $criteria['day'] = $from;
        } else {
            $criteria['day@min'] = $from;
            $criteria['day@max'] = $to;
        }

        return $criteria;
    }


}