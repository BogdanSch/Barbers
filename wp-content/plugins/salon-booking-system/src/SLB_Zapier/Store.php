<?php

namespace SLB_Zapier;

class Store {

    const NEW_BOOKINGS_KEY = 'sln_zapier_new_bookings_ids';

    public function __construct() {
	add_action('sln.booking_builder.create.booking_created', array($this, 'add_new_booking'));
    add_action( 'wp_insert_post', array($this, 'add_booking'), 10, 3 );
    }

    public function add_new_booking($booking) {
	self::update_new_bookings_ids(array_merge(array($booking->getId()), self::get_new_bookings_ids()));
    }

    public function add_booking($post_ID, $post, $update) {

        if ($update || $post->post_type !== \SLN_Plugin::POST_TYPE_BOOKING) {
            return;
        }

        self::update_new_bookings_ids(array_merge(array($post_ID), self::get_new_bookings_ids()));
    }

    public static function get_new_bookings_ids() {
	return get_option(self::NEW_BOOKINGS_KEY, array());
    }

    public static function update_new_bookings_ids($bookings_ids) {
	update_option(self::NEW_BOOKINGS_KEY, $bookings_ids);
    }

}
