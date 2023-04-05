<?php

class SLN_Helper_Availability_Advanced_DayBookings extends SLN_Helper_Availability_AbstractDayBookings
{
    /**
     * @return DateTime
     */
    public function getTime($hour = null, $minutes = null)
    {
        if (!isset($hour)) {
            $hour = $this->getDate()->format('H');
        }
        $now = clone $this->getDate();
        $now->setTime($hour, $minutes ? $minutes : 0);

        return $now;
    }

    protected function buildTimeslots()
    {
        $ret = array();
        $formattedDate = $this->getDate()->format('Y-m-d');

        foreach($this->minutesIntervals as $t) {
            $ret[$t] = array('booking' => array(), 'service' => array(), 'attendant' => array(),'holidays' => array());
            if($this->holidays){
                foreach ($this->holidays as $holiday){
                    $hData = $holiday->getData();
                    if( !$holiday->isValidTime($formattedDate.' '.$t)) $ret[$t]['holidays'][] = $hData;
                }
            }
        }
        $settings = SLN_Plugin::getInstance()->getSettings();
        $bookingOffsetEnabled = $settings->get('reservation_interval_enabled');
        $bookingOffset = $settings->get('minutes_between_reservation');

        /** @var SLN_Wrapper_Booking[] $bookings */
        $bookings = apply_filters('sln_build_timeslots_bookings_list', $this->bookings, $this->date, $this->currentBooking);
        foreach ($bookings as $booking) {
            $bookingServices = $booking->getBookingServices();
            foreach ($bookingServices->getItems() as $bookingService) {
                $times = SLN_Func::filterTimes(
                    $this->minutesIntervals,
                    $bookingService->getStartsAt(),
                    $bookingService->getEndsAt()
                );
                foreach ($times as $time) {
                    $time = $time->format('H:i');
                    if (!in_array($booking->getId(), $ret[$time]['booking']) && apply_filters('sln_build_timeslots_add_booking_to_timeslot', true, $time, $booking, $this->bookings)) {
                        $ret[$time]['booking'][] = $booking->getId();
                    }
                    if ($bookingService->getService() && apply_filters('sln_build_timeslots_add_service_to_timeslot', true, $time, $bookingService, $booking, $this->bookings)) {
                    @$ret[$time]['service'][$bookingService->getService()->getId()]++;
                    }
                }

                if ($bookingServices->isLast($bookingService) && $bookingOffsetEnabled) {
                    $offsetStart = $booking->getEndsAt();
                    $offsetEnd = clone $booking->getEndsAt();
                    $offsetEnd->modify('+'.$bookingOffset.' minutes');
                    $times = SLN_Func::filterTimes($this->minutesIntervals, $offsetStart, $offsetEnd);
                    foreach ($times as $time) {
                        $time = $time->format('H:i');
                        if (apply_filters('sln_build_timeslots_add_booking_to_timeslot', true, $time, $booking, $this->bookings)
                        ) {
                            $ret[$time]['booking'][] = $booking->getId();
                            foreach ($bookingServices->getItems() as $bookingService) {
                                if ($bookingService->getService()) {
                                    @$ret[$time]['service'][$bookingService->getService()->getId()]++;
                                }
                            }
			}
                    }
                }
            }
        }


        $bookings = $this->allBookings;
        foreach ($bookings as $booking) {
            $bookingServices = $booking->getBookingServices();
            foreach ($bookingServices->getItems() as $bookingService) {
                $times = SLN_Func::filterTimes(
                    $this->minutesIntervals,
                    $bookingService->getStartsAt(),
                    $bookingService->getEndsAt()
                );
                foreach ($times as $time) {
                    $time = $time->format('H:i');
                    if($bookingService->getAttendant() && @!is_array($bookingService->getAttendant())){
                        if ($bookingService->getService() && apply_filters('sln_build_timeslots_add_attendant_to_timeslot', true, $time, $bookingService, $booking, $this->bookings)) {
                            @$ret[$time]['attendant'][$bookingService->getAttendant()->getId()]++;
                            @$ret[$time]['attendant_service'][$bookingService->getAttendant()->getId()][] = $bookingService->getService()->getId();
                        }
                    }elseif($bookingService->getAttendant() && @is_array($bookingService->getAttendant())){
                        $service = $bookingService->getService();
                        foreach($bookingService->getAttendant() as $attendant){
                            if(!empty($service) && !empty($attendant) && apply_filters('sln_build_timeslots_add_attendant_to_timeslot', true, $time, $bookingService, $booking, $this->bookings)){
                                @$ret[$time]['attendant'][$attendant->getId()]++;
                                @$ret[$time]['attendant_service'][$attendant->getId()][] = $service->getId();
                            }
                        }
                    }
                }

                if ($bookingServices->isLast($bookingService) && $bookingOffsetEnabled) {
                    $offsetStart = $booking->getEndsAt();
                    $offsetEnd = clone $booking->getEndsAt();
                    $offsetEnd->modify('+'.$bookingOffset.' minutes');
                    $times = SLN_Func::filterTimes($this->minutesIntervals, $offsetStart, $offsetEnd);
                    foreach ($times as $time) {
                        $time = $time->format('H:i');
			if (apply_filters('sln_build_timeslots_add_booking_to_timeslot', true, $time, $booking, $this->bookings)
			) {
                            foreach ($bookingServices->getItems() as $bookingService) {
                                if ($bookingService->getService() && $bookingService->getAttendant()) {
                                    $attendant = $bookingService->getAttendant();
                                    if(!is_array($attendant)){
                                        @$ret[$time]['attendant'][$bookingService->getAttendant()->getId()]++;
                                        @$ret[$time]['attendant_service'][$bookingService->getAttendant()->getId()][] = $bookingService->getService()->getId();
                                    }else{
                                        foreach($attendant as $attObj){
                                            @$ret[$time]['attendant'][$attObj->getId()]++;
                                            @$ret[$time]['attendant_service'][$attObj->getId()][] = $bookingService->getService()->getId();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $ret;
    }
}
