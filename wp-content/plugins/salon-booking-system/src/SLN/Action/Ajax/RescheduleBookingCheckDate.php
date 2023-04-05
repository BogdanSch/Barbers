<?php

class SLN_Action_Ajax_RescheduleBookingCheckDate extends SLN_Action_Ajax_Abstract {
	public function execute() {
		$handler = new SLN_Action_Ajax_CheckDateAlt( $this->plugin );

		$date = sanitize_text_field( wp_unslash( $_POST['_sln_booking_date'] ) );
		$time = sanitize_text_field( wp_unslash( $_POST['_sln_booking_time'] ) );

		$timezone = sanitize_text_field( wp_unslash( $_POST['customer_timezone'] ) );

                if ($this->plugin->getSettings()->isDisplaySlotsCustomerTimezone() && $timezone) {
                    $date = SLN_Func::filter( sanitize_text_field( wp_unslash( $_POST['_sln_booking_date'] ) ), 'date' );
                    $time = SLN_Func::filter( sanitize_text_field( wp_unslash( $_POST['_sln_booking_time'] ) ), 'time' );
                    $dateTime = (new SLN_DateTime($date . ' ' . $time, new DateTimeZone($timezone)))->setTimezone(SLN_DateTime::getWpTimezone());
                    $date = $this->plugin->format()->date($dateTime);
                    $time = $this->plugin->format()->time($dateTime);
                }

		$services = $_POST['_sln_booking']['services'] ?? array();

		$handler->setDate( $date );
		$handler->setTime( $time );

		$bookingID = $_POST['_sln_booking_id'];

		$booking = SLN_Plugin::getInstance()->createBooking( $bookingID );

		$handler->setBooking( $booking );

		$bb = $this->plugin->getBookingBuilder();

		$bb->clear();

		$date = SLN_Func::filter( sanitize_text_field( wp_unslash( $_POST['_sln_booking_date'] ) ), 'date' );
		$time = SLN_Func::filter( sanitize_text_field( wp_unslash( $_POST['_sln_booking_time'] ) ), 'time' );

                if ($this->plugin->getSettings()->isDisplaySlotsCustomerTimezone() && $timezone) {
                    $dateTime = (new SLN_DateTime($date . ' ' . $time, new DateTimeZone($timezone)))->setTimezone(SLN_DateTime::getWpTimezone());
                    $date = $dateTime->format('Y-m-d');
                    $time = $dateTime->format('H:i');
                }

		$bb->setDate( $date );
		$bb->setTime( $time );

		$bb->setServicesAndAttendants( $services );

		$bb->save();

		$handler->checkDateTime();

		$errors = $handler->getErrors();

		if ( $errors ) {
			$ret = compact( 'errors' );
		} else {
			$ret = array( 'success' => 1 );
		}

		$ret['intervals'] = $handler->getIntervalsArray($this->plugin->getSettings()->isDisplaySlotsCustomerTimezone() ? $timezone : '');

		$bb->clear();

		return $ret;
	}

	public function getIntervals( $date, $time, array $services = array() ) {
		$handler = new SLN_Action_Ajax_CheckDateAlt( $this->plugin );

		$handler->setDate( $date );
		$handler->setTime( $time );

		$bb = $this->plugin->getBookingBuilder();

		$bb->clear();

		$bb->setDate( $date );
		$bb->setTime( $time );

		$bb->setServicesAndAttendants( $services );

		$bb->save();

		return $handler->getIntervalsArray();
	}

}