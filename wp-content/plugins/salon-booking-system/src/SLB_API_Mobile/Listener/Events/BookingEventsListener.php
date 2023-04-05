<?php

namespace SLB_API_Mobile\Listener\Events;

use SLN_Plugin;
use WP_User_Query;
use SLB_API_Mobile\Third\OnesignalAPI;

class BookingEventsListener
{
    public function __construct()
    {
	add_action('sln.booking_builder.create.booking_created', array($this, 'event_created'), 10, 1);
    }

    public function event_created( $booking ) {

	$plugin   = SLN_Plugin::getInstance();
	$settings = $plugin->getSettings();

	if ( ! $settings->get('onesignal_new') || ! $booking ) {
	    return;
	}

	$query = new WP_User_Query(array(
	    'meta_query' => array(
		array(
		    'key'     => '_sln_onesignal_player_id',
		    'value'   => '',
		    'compare' => '!=',
		),
	    )
        ));

	$player_ids = array();

	foreach ($query->results as $user) {

	    $user_player_ids = $user->get('_sln_onesignal_player_id');

	    if ( ! is_array( $user_player_ids ) ) {
		$user_player_ids = array($user_player_ids);
	    }

	    $player_ids = array_merge($player_ids, $user_player_ids);
	}

	$player_ids = array_values(array_unique($player_ids));

	if ( ! $player_ids ) {
	    return;
	}

	$app_id  = $settings->get('onesignal_app_id');
	$message = $plugin->loadView('onesignal/notify', compact('booking'));
        $url     = home_url(add_query_arg(array('tab' => 'reservations-calendar', 'booking_id' => $booking->getId()), 'salon-booking-pwa'));

	try {
	    OnesignalAPI::notify($app_id, $player_ids, $message, array('booking_id' => $booking->getId()), $url);
	} catch (\Exception $ex) {

	}
    }

}