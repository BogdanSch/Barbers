<?php

class SLN_Wrapper_Booking extends SLN_Wrapper_Abstract
{
    private $bookingServices;
    private $attendants;

    const _CLASS = 'SLN_Wrapper_Booking';

    public function getPostType()
    {
        return SLN_Plugin::POST_TYPE_BOOKING;
    }

    function getAmount()
    {
        $ret = $this->getMeta('amount');
        $ret = empty($ret) ? 0 : floatval($ret);

        return $ret;
    }

    function getDeposit()
    {
        $ret = $this->getMeta('deposit');
        $ret = empty($ret) ? 0 : floatval($ret);

        return $ret;
    }

    function getToPayAmount($format = true, $include_fee = true)
    {
        $ret = $this->getDeposit() > 0 ? $this->getDeposit() : $this->getAmount();

	if ( $include_fee ) {
	    $ret += SLN_Helper_TransactionFee::getFee($ret);
	}

        return $format ? number_format($ret, 2) : $ret;
    }

    function getRemaingAmountAfterPay($format = true)
    {
        $ret = $this->getDeposit() > 0 ? ( $this->getAmount() - $this->getDeposit() ) : 0;

        return $format ? number_format($ret, 2) : $ret;
    }

    function getFirstname()
    {
        return $this->getMeta('firstname');
    }

    function getLastname()
    {
        return $this->getMeta('lastname');
    }

    function getDisplayName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    function getEmail()
    {
        return $this->getMeta('email');
    }

    function getPhone()
    {
        return $this->getMeta('phone');
    }

    function getAddress()
    {
        return $this->getMeta('address');
    }


    function getTime($timezone = null)
    {
        return new SLN_DateTime($this->getMeta('time'),$timezone);
    }

    function getDate($timezone = null)
    {
        return new SLN_DateTime($this->getMeta('date'),$timezone);
    }

    function getServicesMeta()
    {
        $data = $this->getMeta('services');
        $data = empty($data) ? array() : $data;

        return $data;
    }

    function getBookingServices()
    {
        if (!$this->bookingServices) {
            $this->maybeProcessBookingServices();
            $this->bookingServices = SLN_Wrapper_Booking_Services::build($this->getServicesMeta(), $this->getStartsAt(), 0, $this->getCountServices());
        }

        return $this->bookingServices;
    }

    function getOnProcess() {
        $ret = $this->getMeta('on_process');
        $ret = empty($ret) ? false : boolval($ret);

        return $ret;
    }

    function setOnProcess($on_process) {
        $this->setMeta('on_process', $on_process);
    }

    function maybeProcessBookingServices()
    {

        $servicesProcessed = $this->getMeta('services_processed');

        if (empty($servicesProcessed)) {
            $this->evalBookingServices();
        }
    }

    function evalBookingServices()
    {
        $data = $this->getServicesMeta();
        $this->bookingServices = SLN_Wrapper_Booking_Services::build($data, $this->getStartsAt(), 0, $this->getCountServices());
        $ret = $this->bookingServices->toArrayRecursive();
        $this->setMeta('services', $ret);
        $this->setMeta('services_processed', 1);
    }

    function getDuration()
    {
        $ret = $this->getMeta('duration');
        if (empty($ret)) {
            $ret = '00:00';
        }
        $ret = SLN_Func::filter($ret, 'time');
        if ($ret == '00:00') {
            $ret = $this->evalDuration();
        }

        $ret = SLN_Func::getMinutesFromDuration($ret)*60;
        $date = new SLN_DateTime('@'.$ret);

        return  $date;
    }

    function evalDuration()
    {
        $settings = SLN_Plugin::getInstance()->getSettings();
        if('basic' !== $settings->getAvailabilityMode()){
            $h = 0;
            $i = 0;
			$max = 0;
            SLN_Plugin::addLog(__CLASS__.' eval duration of'.$this->getId());
            foreach ($this->getBookingServices()->getItems() as $bookingService) {
                $d = $bookingService->getTotalDuration();
	            $dInMinutes = intval($d->format('H')) * 60 + intval($d->format('i'));
	            if ($bookingService->getParallelExec()) {
		            if ($dInMinutes > $max) {
			            $max = $dInMinutes;
		            }
	            } else {
		            $i += $dInMinutes;
	            }
                SLN_Plugin::addLog(' - service '.$bookingService.' +'.$d->format('H:i'));
            }
	        $i += $max;
            if ($i == 0) {
                $i = $settings->getInterval();
            }
        }else{
            $i = $settings->getInterval();
        }
        $str = SLN_Func::convertToHoursMins($i);
        $this->setMeta('duration', $str);

        return $str;
    }

    function evalTotal()
    {
	    $settings = SLN_Plugin::getInstance()->getSettings();

        $amount = 0;
        SLN_Plugin::addLog(__CLASS__.' eval total of'.$this->getId());
        foreach ($this->getBookingServices()->getItems() as $bookingService) {
            $price   = $bookingService->getPrice();
            $amount += $price;
            SLN_Plugin::addLog(' - service '.$bookingService->getService().' +'.$price);
        }
        $settings = SLN_Plugin::getInstance()->getSettings();
        if($settings->get('enable_booking_tax_calculation') && 'inclusive' !== $settings->get('enter_tax_price')){
            $amount = $amount * (1 + floatval($settings->get('tax_value')) / 100);
        }

        $amount += $this->getTips();
        SLN_Plugin::addLog(' - tips +'.$this->getTips());

            $this->setMeta('amount', $amount);

        $depositAmount = $settings->getPaymentDepositAmount();
        if ($settings->isPaymentDepositFixedAmount()) {
                $deposit = min($amount, $depositAmount);
        }
        else {
                $deposit = ($amount / 100) * $depositAmount;
        }

        $this->setMeta('deposit', $deposit);

        return $amount;
    }


    function hasAttendant(SLN_Wrapper_Attendant $attendant)
    {
        return in_array($attendant->getId(), $this->getAttendantsIds());
    }

    function hasService(SLN_Wrapper_Service $service)
    {
        return in_array($service->getId(), $this->getServicesIds());
    }

    /**
     * @param bool|false $unique
     *
     * @return array
     */
    function getAttendantsIds($unique = false)
    {
        $post_id = $this->getId();
        $data = apply_filters('sln_booking_attendants', get_post_meta($post_id, '_sln_booking_services', true));
        $ret = array();
        if (is_array($data)) {
            foreach ($data as $item) {
                if($item['attendant'])
                    $ret[$item['service']] = $item['attendant'];
            }
        }

        return $unique ? array_unique($ret, SORT_NUMERIC) : $ret;
    }

    /**
     * @return SLN_Wrapper_Attendant|false
     */
    public function getAttendant()
    {
        $ret = $this->getAttendants();
        return empty($ret) ? false : array_pop($ret);
    }

    /**
     * @param bool $unique
     *
     * @return SLN_Wrapper_Attendant[]
     */
    public function getAttendants($unique = false)
    {
        if (!$this->attendants) {
            $plugin = SLN_Plugin::getInstance();
            $this->attendants = array();
            $attIds = $this->getAttendantsIds($unique);
            foreach ($attIds as $service_id => $id) {
                if (!$id) {
                    continue;
                }
                /** @var SLN_Wrapper_Attendant $tmp */
                $tmp = $plugin->createAttendant($id);
                if(!is_array($id)){
                    if (!$tmp->isEmpty()) {
                        $this->attendants[$service_id] = $tmp;
                    }
                }else{
                    if(!in_array(true, SLN_Wrapper_Attendant::getArrayAttendantsValue('isEmpty', $tmp))){
                        $this->attendants[$service_id] = $tmp;
                    }
                }
            }
        }

        return $this->attendants;
    }

    function getAttendantsString()
    {
        $attendants = $this->getAttendants(true);
        if (empty($attendants)) {
            return '';
        } else {
            $ret = array();
            foreach ($attendants as $attendant) {
                if(!is_array($attendant)){
                    $ret[] = $attendant->getName();
                }else{
                    $ret = array_merge($ret, SLN_Wrapper_Attendant::getArrayAttendantsValue('getName', $attendant));
                }
            }

            return implode(', ', $ret);
        }
    }

    function getServicesIds()
    {
        $data = $this->getMeta('services');
        $ret = array();
        if (is_array($data)) {
            foreach ($data as $item) {
                $ret[] = $item['service'];
            }
        }

        return $ret;
    }

    /**
     * @return SLN_Wrapper_Service[]
     */
    function getServices()
    {
        $ret = array();
        foreach ($this->getServicesIds() as $id) {
            $tmp = new SLN_Wrapper_Service($id);
            if (!$tmp->isEmpty()) {
                $ret[] = $tmp;
            }
        }

        return $ret;
    }

    function getNote()
    {
        return $this->getMeta('note');
    }

    function getAdminNote()
    {
        return $this->getMeta('admin_note');
    }


    function getTransactionId()
    {
        return $this->getMeta('transaction_id');
    }

    function getStartsAt( $timezone='' )
    {
		if($timezone)
			return new SLN_DateTime($this->getDate()->format('Y-m-d').' '.$this->getTime()->format('H:i'), new DateTimeZone($timezone) );
		else
			return new SLN_DateTime($this->getDate()->format('Y-m-d').' '.$this->getTime()->format('H:i'));
    }

    function getEndsAt( $timezone='' )
    {
        $start = $this->getStartsAt( $timezone );
        $minutes = SLN_Func::getMinutesFromDuration($this->getDuration());
        if ($minutes == 0) {
            $minutes = 60;
        }
        $start->modify('+'.$minutes.' minutes');

        return $start;
    }

    public function getUserId()
    {
        return $this->object->post_author;
    }

	/**
     * @return null|SLN_Wrapper_Customer
     */
    public function getCustomer()
    {
        $customer = new SLN_Wrapper_Customer($this->getUserId());
        if ($customer->isEmpty()) {
            $customer = null;
        }

        return $customer;
    }

    function isNew()
    {
        return strpos($this->object->post_status, 'sln-b-') !== 0;
    }

    public function markPaid($transactionId)
    {
        $this->setMeta('transaction_id', $transactionId);
        $this->setStatus(SLN_Enum_BookingStatus::PAID);

	do_action('sln_booking_mark_paid_after', $this);
    }

    public function getPayUrl()
    {
        $payUrl = add_query_arg(
            array(
                'sln_step_page' => 'thankyou',
                'submit_thankyou' => 1,
                'sln_booking_id' => $this->getUniqueId(),
            ),
            get_permalink(SLN_Plugin::getInstance()->getSettings()->getPayPageId() )
        );

	return apply_filters('sln.booking.get-pay-url', $payUrl, $this);
    }

    public function getRescheduleUrl()
    {
        return add_query_arg(
            array(
                'booking_id'		 => $this->getId(),
                'sln_reschedule_booking' => 1,
            ),
            get_permalink(SLN_Plugin::getInstance()->getSettings()->getPayPageId() )
        );
    }

    public function getCancelUrl()
    {
	return add_query_arg(
            array('booking_id' => $this->getUniqueId()),
            SLN_Action_CancelBookingLink::getUrl()
        );
    }

    public function getTimeStringToChangeStatusFromPending() {
        $plugin = SLN_Plugin::getInstance();
        $left   = '';

        if (in_array($this->getStatus(), array(SLN_Enum_BookingStatus::PENDING, SLN_Enum_BookingStatus::PENDING_PAYMENT, SLN_Enum_BookingStatus::DRAFT)) && $plugin->getSettings()->get('pay_offset_enabled')) {
            $payOffset      = $plugin->getSettings()->get('pay_offset');
            $checkTimestamp = SLN_TimeFunc::getPostTimestamp($this->object);
            $leftSeconds    = $checkTimestamp + $payOffset*MINUTE_IN_SECONDS - time();
            $leftMinutes    = $leftSeconds > 0 ? $leftSeconds/60 : 0;

            $left = sprintf(__("%d hours and %d minutes", 'salon-booking-system'), (int) $leftMinutes/60, (int) $leftMinutes%60);
        }

        return $left;
    }

    public function getUniqueId()
    {
        $id = $this->getMeta('uniqid');
        if (!$id) {
            $id = md5(uniqid().$this->getId());
            $this->setMeta('uniqid', $id);
        }

        return $this->getId().'-'.$id;
    }

    public function getRating()
    {
        return $this->getMeta('rating');
    }

    public function setRating($rating)
    {
        $this->setMeta('rating', $rating);
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $oldStatus = $this->getStatus();
        $ret       = parent::setStatus($status);
        do_action('sln.booking.setStatus', $this, $oldStatus, $this->getStatus());

        $this->evalCustomerDetails();

        return $ret;
    }

    public function evalCustomerDetails()
    {
        if (SLN_Wrapper_Customer::isCustomer($this->getUserId())) {
            $customer = new SLN_Wrapper_Customer($this->getUserId());

            $thisBookingTime = $this->getStartsAt()->getTimestamp();
            $lastBookingTime = $customer->getLastBookingTime();

            $allStatuses          = array_keys(SLN_Enum_BookingStatus::toArray());
            $completedStatuses    = array(SLN_Enum_BookingStatus::PAY_LATER, SLN_Enum_BookingStatus::PAID, SLN_Enum_BookingStatus::CONFIRMED);
            $nonCompletedStatuses = array_diff($allStatuses, $completedStatuses);


            if (in_array($this->getStatus(), $nonCompletedStatuses) && $thisBookingTime === $lastBookingTime) {
                $customer->setLastBookingTime($customer->calcLastBookingTime());
            }
            elseif (in_array($this->getStatus(), $completedStatuses) && ($thisBookingTime > $lastBookingTime || !$lastBookingTime)) {
                $customer->setLastBookingTime($thisBookingTime);
            }

            $customer->setNextBookingTime($customer->calcNextBookingTime());
        }
    }

    public function getEmailCancellationDetails(&$cancellationText, &$bookingMyAccountUrl)
    {
		$cancellationText = $bookingMyAccountUrl = '';
		$plugin = SLN_Plugin::getInstance();

		$cancellationEnabled = $plugin->getSettings()->get('cancellation_enabled');
		if( !$cancellationEnabled )
			return false;

		$cancellationHours = $plugin->getSettings()->get('hours_before_cancellation');
		$outOfTime = ($this->getStartsAt()->getTimeStamp() - time()) < $cancellationHours * 3600;
		if( $outOfTime )
			return false;

		$bookingMyAccountPageId = $plugin->getSettings()->getBookingmyaccountPageId();
		if( !$bookingMyAccountPageId )
			return false;

		// have time and know page ?
		$cancellationText = $cancellationHours<24 ? $cancellationHours . __(" hours", 'salon-booking-system') :
							($cancellationHours==24? __("1 day", 'salon-booking-system') : round($cancellationHours/24) . __("days", 'salon-booking-system'));
		$bookingMyAccountUrl = get_permalink($bookingMyAccountPageId);
		return true;
	}

    function getTips()
    {
        $data = $this->getMeta('tips');
        $data = empty($data) ? 0 : (float)$data;

        return $data;
    }

    function getNotifyCustomer() {
        return !$this->getMeta('dont_notify_customer');
    }

    function getSmsPrefix() {
        return $this->getMeta('sms_prefix');
    }

    function getCustomerTimezone() {
        return $this->getMeta('customer_timezone');
    }

    function getCountServices() {
        return $this->getMeta('service_count') ? $this->getMeta('service_count') : array();
    }
}
