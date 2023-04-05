<?php

use Salon\Util\Date;
use Salon\Util\Time;

class SLN_Action_Ajax_CheckDateAlt extends SLN_Action_Ajax_CheckDate
{
	/**
	 * @param array        $services
	 * @param SLN_DateTime $datetime
	 *
	 * @return bool
	 */
	private function checkDayServicesAndAttendants($services, $datetime) {
        $bb  = $this->plugin->getBookingBuilder();
		$bookingServices = SLN_Wrapper_Booking_Services::build($services, $datetime, 0, $bb->getCountServices());
		$date            = Date::create($datetime->format('Y-m-d'));
		foreach ($bookingServices->getItems() as $bookingService) {
			/** @var SLN_Helper_AvailabilityItems $avServiceItems */
			$avServiceItems = $bookingService->getService()->getAvailabilityItems();
			if(!$avServiceItems->isValidDate($date)) {
				return false;
			}

			$attendant = $bookingService->getAttendant();
			if (!empty($attendant)) {
				/** @var SLN_Helper_AvailabilityItems $avAttendantItems */
                if(!is_array($attendant)){
                    $avAttendantItems = $attendant->getAvailabilityItems();
                    if(!$avAttendantItems->isValidDate($date)) {
                        return false;
                    }
                }else{
                    foreach($attendant as $att){
                        $avAttendantItems = $att->getAvailabilityItems();
                        if(!$avAttendantItems->isValidDate($date)){
                            return false;
                        }
                    }
                }
			}
		}

		return true;
	}

    public function getIntervalsArray($timezone = '') {
        if ($this->isAdmin()) {
            return parent::getIntervalsArray();
        }

        $fullDays = array();
        $plugin = $this->plugin;
        $ah   = $plugin->getAvailabilityHelper();
        $bc = $plugin->getBookingCache();
        $hb = $ah->getHoursBeforeHelper();
        $dateTimeLog = SLN_Helper_Availability_AdminRuleLog::getInstance();

        $bb = $plugin->getBookingBuilder();
        $bservices = $bb->getAttendantsIds();
        $this->setDuration(new Time($bb->getDuration()));
        $intervals = parent::getIntervals();
        $intervalsArray = array();
        foreach($intervals->getDates() as $k => $v) {
            $available = false;
            $tmpDate   = new SLN_DateTime($v->getDateTime());
            $dateLog = $v->getDateTime()->format('Y-m-d');
            $dateTimeLog->addDateLog( $dateLog, $this->checkDayServicesAndAttendants($bservices, $tmpDate), __( 'The attendant is unavailable on this day', 'salon-booking-system' ) );
            if ($this->checkDayServicesAndAttendants($bservices, $tmpDate)) {
	            $ah->setDate($tmpDate, $this->booking);
                $times = $bc->getDay(Date::create($tmpDate))['free_slots'];
	            foreach ($times as $time) {
                    $d = $v->getDateTime()->format('Y-m-d');
                    $tmpDateTime = new SLN_DateTime("$d $time");
                    if(!$hb->check($tmpDateTime)) {
                        continue;
                    }
		            $errors = $this->checkDateTimeServicesAndAttendants($bservices, $tmpDateTime);
		            if (empty($errors)) {
			            $available = true;
			            break;
		            }
	            }
            }
            $dateTimeLog->addDateLog( $dateLog, $available, __( 'There are no free time slots on this day', 'salon-booking-system' ) );

            if (!$available) {
                $fullDays[] = $v->getDateTime();
            } else {
                $intervalsArray['dates'][$k] = $v;
            }
        }

        if(empty($intervalsArray['dates'])) {
            $intervalsArray = $intervals->toArray($timezone);
            $intervalsArray['dates'] = array();
            $intervalsArray['times'] = array();
            return $intervalsArray;
        }

        $suggestedDate = $intervals->getSuggestedDate()->format('Y-m-d');
        if (array_search($suggestedDate, array_map(function ($date) { return $date->getDateTime()->format('Y-m-d'); }, $intervalsArray['dates'])) === false) {
            $suggestedDate = reset($intervalsArray['dates'])->getDateTime()->format('Y-m-d');
            $intervals->setDatetime(new SLN_DateTime($suggestedDate), $this->duration);
        }
        $tmpDate = new SLN_DateTime($suggestedDate);

        $ah->setDate($tmpDate, $this->booking);
        $times = $ah->getCachedTimes(Date::create($tmpDate), $this->duration);

        //for SLB_API_Mobile purposes
        $customTimeFormat = $_GET['time_format'] ?? false;

        foreach ($times as $k => $t) {
            $time = $t->format('H:i');
            $tmpDateTime = new SLN_DateTime("$suggestedDate $time");
            $ah->setDate($tmpDateTime, $this->booking);
            $errors = $this->checkDateTimeServicesAndAttendants($bservices, $tmpDateTime, true);
            
            if (empty($errors)) {
                $intervalsArray['times'][$k] = $t;
                $dateTimeLog->addLog( $t->format('H:i'), empty($errors), __( 'Time is free for services and attendants.', 'salon-booking-system') );
            }else{
                $dateTimeLog->addArrayErrors( $t->format('H:i'), $errors );
            }
        }

        $intervalsArray['suggestedTime'] = $intervals->getSuggestedDate()->format($customTimeFormat ?: 'H:i');

        if (!isset($intervalsArray['times'][$intervals->getSuggestedDate()->format('H:i')])) {
            $tmpTime = new SLN_DateTime(reset($intervalsArray['times'])->format('H:i'));
            $intervalsArray['suggestedTime'] = $tmpTime->format($customTimeFormat ?: 'H:i');
        }

        $tmpDate = $timezone ? (new SLN_DateTime($suggestedDate . ' ' . $intervalsArray['suggestedTime']))->setTimezone(new DateTimezone($timezone)) : new SLN_DateTime($suggestedDate . ' ' . $intervalsArray['suggestedTime']);

        $intervalsArray['suggestedTime']  = $plugin->format()->time($tmpDate, $customTimeFormat);
        $intervalsArray['suggestedDate']  = $plugin->format()->date($tmpDate);
        $intervalsArray['suggestedYear']  = $tmpDate->format('Y');
        $intervalsArray['suggestedMonth'] = $tmpDate->format('m');
        $intervalsArray['suggestedDay']   = $tmpDate->format('d');
        $intervalsArray['universalSuggestedDate'] = $tmpDate->format('Y-m-d');

        $fullDays = array_merge($intervals->getFullDays(), $fullDays);

        $years = array();

        foreach ($intervals->getYears() as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $intervalsArray['years'][$v->format('Y')] = $v->format('Y');
        }

        $months = SLN_Func::getMonths();
        $monthsList = array();

        foreach ($intervals->getMonths() as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $intervalsArray['months'][$v->format('m')] = $months[intval($v->format('m'))];
        }

        $days = array();

        foreach ($intervals->getDays() as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $intervalsArray['days'][$v->format('d')] = $v->format('d');
        }

        $workTimes = array();

        foreach ($intervals->getWorkTimes() as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $intervalsArray['workTimes'][$v->format($customTimeFormat ?: 'H:i')] = $v->format($customTimeFormat ?: 'H:i');
        }

        $dates = array();

        foreach ($intervalsArray['dates'] as $v) {
            $dates[] = $v->getDateTime()->format('Y-m-d');
        }

        $intervalsArray['dates'] = $dates;

        $times = array();

        foreach ($intervalsArray['times'] as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $times[$v->format($customTimeFormat ?: 'H:i')] = $v->format($customTimeFormat ?: 'H:i');
        }
        
        $intervalsArray['times'] = $times;

        foreach ($fullDays as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $intervalsArray['fullDays'][] = $v->format('Y-m-d');
        }

        return $intervalsArray;
    }

    public function isAdmin() {
        return isset($_POST['post_ID']);
    }

    public function checkDateTime()
    {
        parent::checkDateTime();
        if ($this->isAdmin()) {
            return;
        }

        $plugin = $this->plugin;
        $errors = $this->getErrors();

        if (empty($errors)) {
            $date   = $this->getDateTime();

            $bb = $plugin->getBookingBuilder();
            $bservices = $bb->getAttendantsIds();

            $errors = $this->checkDateTimeServicesAndAttendants($bservices, $date);

            foreach($errors as $error) {
                $this->addError($error);
            }
        }

    }

    public function checkDateTimeServicesAndAttendants($services, $date, $check_duration = false) {
        $errors = array();

        $plugin = $this->plugin;
        $ah     = $plugin->getAvailabilityHelper();
        $ah->setDate($date, $this->booking);

        $isMultipleAttSelection = SLN_Plugin::getInstance()->getSettings()->get('m_attendant_enabled');
        $bookingOffsetEnabled   = SLN_Plugin::getInstance()->getSettings()->get('reservation_interval_enabled');
        $bookingOffset          = SLN_Plugin::getInstance()->getSettings()->get('minutes_between_reservation');

        $bb = $this->plugin->getBookingBuilder();
        $bookingServices = SLN_Wrapper_Booking_Services::build($services, $date, 0, $bb->getCountServices());

        $firstSelectedAttendant = null;


        foreach($bookingServices->getItems() as $bookingService) {
            $serviceErrors   = array();
            $attendantErrors = array();

            if ($bookingServices->isLast($bookingService) && $bookingOffsetEnabled) {
                $offsetStart   = $bookingService->getEndsAt();
                $offsetEnd     = $bookingService->getEndsAt()->modify('+'.$bookingOffset.' minutes');
                $serviceErrors = $ah->validateTimePeriod($offsetStart, $offsetEnd);
            }
            if (empty($serviceErrors)) {
                $serviceErrors = $ah->validateBookingService($bookingService, $bookingServices->isLast($bookingService));
            }
            if (!empty($serviceErrors)) {
                $errors[] = $serviceErrors[0];
                continue;
            }

            if ($bookingService->getAttendant() === false) {
                continue;
            }
            $attendant = $bookingService->getAttendant();

            if (!$isMultipleAttSelection && !is_array($attendant)) {
                if (!$firstSelectedAttendant) {
                    $firstSelectedAttendant = $attendant->getId();
                }
                if ($attendant->getId() != $firstSelectedAttendant) {
                    $attendantErrors = array(__('Multiple attendants selection is disabled. You must select one attendant for all services.', 'salon-booking-system'));
                }
            }
            if (empty($attendantErrors)) {
                $attendantErrors = $ah->validateAttendantService(
                    $bookingService->getAttendant(),
                    $bookingService->getService()
                );
                if (empty($attendantErrors)) {
                    if(!is_array($attendant)){
                        $attendantErrors = $ah->validateBookingAttendant($bookingService, $bookingServices->isLast($bookingService));
                    }else{
                        $attendantErrors = $ah->validateBookingAttendants($bookingService, $bookingServices->isLast($bookingService));
                    }

                    if($check_duration){
                        $durationMinutes = SLN_Func::getMinutesFromDuration($bookingService->getTotalDuration());
                        if($durationMinutes){
                            $endAt = clone $date;
                            $endAt->modify('+' . ($durationMinutes - 1) . 'minutes');
                            $attendant = $bookingService->getAttendant();
                            if(!is_array($attendant)){
                                if ($attendant && $attendant->isNotAvailableOnDate($endAt)) {
                                    $errors[] = SLN_Helper_Availability_ErrorHelper::doAttendantNotAvailable($attendant, $endAt);
                                }
                            }else{
                                foreach($attendant as $att){
                                    if($att && $att->isNotAvailableOnDate($endAt)){
                                        $errors[] = SLN_Helper_Availability_ErrorHelper::doAttendantNotAvailable($att, $endAt);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($attendantErrors)) {
                $errors[] = $attendantErrors[0];
            }
        }

        return $errors;
    }
}
