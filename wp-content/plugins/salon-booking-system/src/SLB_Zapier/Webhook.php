<?php

namespace SLB_Zapier;

class Webhook {

    const ACTION	     = 'sln-zapier';
    const METHOD_AUTH	     = 'auth';
    const METHOD_NEW_BOOKING = 'new-booking';

    protected $plugin;

    public function __construct() {
	$this->plugin = \SLN_Plugin::getInstance();
	add_action('parse_request', array($this, 'handle_request'));
    }

    public function handle_request() {

	if ( empty( $_GET['action'] ) || $_GET['action'] !== self::ACTION ) {
	    return;
	}

	try {

	    $auth_response = $this->handle_auth();

	    $method = empty( $_GET['method'] ) ? '' : $_GET['method'];

	    switch($method) {
		case self::METHOD_AUTH:
		    $response = $auth_response;
		    break;
		case self::METHOD_NEW_BOOKING:
		    $response = $this->handle_new_booking();
		    break;
		default:
		    throw new \Exception('Method does not support', 404);
	    }

	    wp_send_json($response, 200);

	} catch (\Exception $ex) {
	    wp_send_json(array('error' => $ex->getMessage()), $ex->getCode());
	}

	exit();
    }

    protected function handle_auth() {

	if ( empty( $_SERVER['HTTP_X_API_KEY'] ) || $_SERVER['HTTP_X_API_KEY'] !== self::get_api_key() ) {
	    throw new \Exception('Invalid API KEY', 401);
	}

	return array(
	    'salon_name'  => $this->plugin->getSettings()->getSalonName(),
	    'salon_email' => $this->plugin->getSettings()->getSalonEmail(),
	);
    }

    public static function get_url() {
	return add_query_arg('action', self::ACTION, get_home_url());
    }

    public static function get_api_key() {

	$settings = \SLN_Plugin::getInstance()->getSettings();
	$api_key  = $settings->get('zapier_api_key');

	if ( empty( $api_key ) ) {
	    $api_key = self::generate_api_key();
	    $settings->set('zapier_api_key', $api_key);
	    $settings->save();
	}

	return $api_key;
    }

    protected static function generate_api_key() {
	return substr(sha1(self::get_url()."|zapier|sln-booking-plugin|".time()), 0, 20);
    }

    public function handle_new_booking() {

	$bookings_ids = Store::get_new_bookings_ids();

	$bookings   = array();
	$statuses   = empty( $_GET['booking_status'] ) ? array() : $_GET['booking_status'];
	$output_ids = array();

	foreach ($bookings_ids as $i => $id) {
	    try {
		$booking = $this->plugin->createBooking($id);
		if (in_array($booking->getStatus(), $statuses)) {
		    $bookings[]   = $this->get_booking_response($booking);
		    $output_ids[] = $id;
		}
	    } catch (\Exception $ex) {

	    }
	}

	Store::update_new_bookings_ids(array_diff($bookings_ids, $output_ids));

	return $bookings;
    }

    protected function get_booking_response($booking) {

	$booking_services = $booking->getBookingServices() ? $booking->getBookingServices()->getItems() : array();
        $services         = array();

        foreach ($booking_services as $service) {
			if($att = $service->getAttendant()){
				$att = !is_array($att) ? $att->getName() : SLN_Wrapper_Attendant::implodeArrayAttendantsName(', ', $att);
			}else{
				$att = '';
			}
			$services[] = sprintf(
				'%s: %s-%s %s',
				$service->getService()->getName(),
				$this->plugin->format()->time($service->getStartsAt()),
				$this->plugin->format()->time($service->getEndsAt()),
				$att
			);
        }

	$net_total_amount = $booking->getAmount();

	$discount_amount = $booking->getMeta('discount_amount');

	if ($discount_amount) {
	    $discount_amount = array_sum($discount_amount);
	}

	$total_amount = (float)$net_total_amount + (float)$discount_amount;

        $response = array(
            'id'                  => $booking->getId(),
            'created'             => $booking->getPostDate()->format(\DateTime::ISO8601),
            'date'                => $booking->getDate()->format('Y-m-d'),
            'time'                => $booking->getTime()->format('H:i'),
            'status'              => \SLN_Enum_BookingStatus::getLabel($booking->getStatus()),
            'customer_id'         => $booking->getCustomer() ? $booking->getCustomer()->getId() : $booking->getCustomer(),
            'customer_first_name' => $booking->getFirstname(),
            'customer_last_name'  => $booking->getLastname(),
            'customer_email'      => $booking->getEmail(),
            'customer_phone'      => $booking->getPhone(),
            'customer_address'    => $booking->getAddress(),
            'services'            => implode(', ', $services),
            'duration'            => $booking->getDuration()->format('H:i'),
            'net_total_amount'    => $this->plugin->format()->moneyFormatted($net_total_amount, true, true),
            'discount_amount'     => $this->plugin->format()->moneyFormatted($discount_amount, false, true),
            'total_amount'        => $this->plugin->format()->moneyFormatted($total_amount, true, true),
            'transaction_id'      => $booking->getTransactionId(),
            'note'                => $booking->getNote(),
        );

	return $response;
    }

}
