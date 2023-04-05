<?php

class SLN_Wrapper_Booking_Builder
{
    protected $plugin;
    protected $data;
    protected $lastId;

    public function __construct(SLN_Plugin $plugin)
    {
        if (session_id() == '' || session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->plugin = $plugin;
        $this->data = isset($_SESSION[__CLASS__]) ? $_SESSION[__CLASS__] : $this->getEmptyValue();
        $this->lastId = isset($_SESSION[__CLASS__.'last_id']) ? $_SESSION[__CLASS__.'last_id'] : null;
    }

    public function save()
    {
        $_SESSION[__CLASS__] = $this->data;
        $_SESSION[__CLASS__.'last_id'] = $this->lastId;
    }

    public function clear($id = null)
    {
        $this->data = $this->getEmptyValue();
        $this->lastId = $id;
        $this->save();
    }

    /**
     * @return $this
     */
    public function removeLastId()
    {
        unset($_SESSION[__CLASS__.'last_id']);
        $this->lastId = null;

        return $this;
    }

    /**
     * @return SLN_Wrapper_Booking
     */
    public function getLastBooking()
    {
        if ($this->lastId) {
            return $this->plugin->createBooking($this->lastId);
        }
    }

    public function getEmptyValue()
    {
        $from = $this->plugin->getSettings()->getHoursBeforeFrom();
        $d = new SLN_DateTime(SLN_TimeFunc::date('Y-m-d H:i:00'));
        $d->modify($from);
        $tmp = $d->format('i');
        $i = SLN_Plugin::getInstance()->getSettings()->getInterval();
        $diff = $tmp % $i;
        if ($diff > 0) {
            $d->modify('+'.($i - $diff).' minutes');
        }

        return array(
            'date' => $d->format('Y-m-d'),
            'time' => $d->format('H:i'),
            'services' => array(),
        );
    }

    public function get($k)
    {
        return isset($this->data[$k]) ? $this->data[$k] : null;
    }

    public function set($key, $val)
    {
        if (empty($val)) {
            unset($this->data[$key]);
        } else {
            $this->data[$key] = $val;
        }
    }

    public function getDate()
    {
        return $this->data['date'];
    }

    public function getTime()
    {
        return $this->data['time'];
    }

    public function getDateTime()
    {
        $ret = new SLN_DateTime($this->getDate().' '.$this->getTime());

        return $ret;
    }

    public function setDate($date)
    {
        $this->data['date'] = $date;

        return $this;
    }

    public function setTime($time)
    {
        $this->data['time'] = $time;

        return $this;
    }

    public function setAttendant(SLN_Wrapper_AttendantInterface $attendant, SLN_Wrapper_ServiceInterface $service)
    {
        if ($this->hasService($service)) {
            $this->data['services'][$service->getId()] = $attendant->getId();
        }
    }

    public function hasAttendant(SLN_Wrapper_AttendantInterface $attendant, SLN_Wrapper_ServiceInterface $service = null)
    {
        if (!isset($this->data['services'])) {
            return false;
        }

        if (is_null($service)) {
            return in_array($attendant->getId(), $this->data['services']);
        } else {
            return isset($this->data['services'][$service->getId()]) && $this->data['services'][$service->getId(
            )] == $attendant->getId();
        }
    }

    public function removeAttendants()
    {
        $this->data['services'] = array_fill_keys(array_keys($this->data['services']), 0);
    }


    public function hasService(SLN_Wrapper_ServiceInterface $service)
    {
        return in_array($service->getId(), array_keys($this->data['services']));
    }

    public function getAttendantsIds()
    {
        return $this->data['services'];
    }

    /**
     * @return SLN_Wrapper_AttendantInterface|false
     */
    public function getAttendant()
    {
        $atts = $this->getAttendants();

        return reset($atts);
    }

    /**
     * @return SLN_Wrapper_AttendantInterface[]
     */
    public function getAttendants($unique=false)
    {
        $ids = $this->getAttendantsIds();
        $ret = array();
        if($unique){
            $ids = array_unique($ids, SORT_NUMERIC);
        }
        foreach ($ids as $service_id => $attendant_id) {
            if ($attendant_id) {
                $ret[$service_id] = $this->plugin->createAttendant($attendant_id);
            }
        }

        return $ret;
    }

    public function setServicesAndAttendants($data) {
        $this->data['services'] = $data;
    }

    public function addService(SLN_Wrapper_ServiceInterface $service)
    {
        if ((!isset($this->data['services'])) || (!in_array($service->getId(), array_keys($this->data['services'])))) {
            $this->data['services'][$service->getId()] = 0;
            uksort($this->data['services'], array('SLN_Repository_ServiceRepository', 'serviceCmp'));
        }
    }

    public function removeService(SLN_Wrapper_ServiceInterface $service)
    {
        if (isset($this->data['services'])) {
            unset($this->data['services'][$service->getId()]);
        }
    }

    public function clearService(SLN_Wrapper_ServiceInterface $service)
    {
        if (isset($this->data['services'][$service->getId()])) {
            $this->data['services'][$service->getId()] = 0;
        }
    }

    public function removeServices()
    {
        $this->data['services'] = array();
    }

    public function getServicesIds()
    {
        return array_keys($this->getServices());
    }

    public function getPrimaryServicesIds()
    {
        return array_keys($this->getPrimaryServices());
    }

    public function getSecondaryServicesIds()
    {
        return array_keys($this->getSecondaryServices());
    }

    /**
     * @param bool $primary
     * @param bool $secondary
     *
     * @return SLN_Wrapper_ServiceInterface[]
     */
    public function getServices($primary = true, $secondary = true)
    {
        $ids = array_keys($this->data['services']);
        $ret = array();
        /** @var SLN_Repository_ServiceRepository $repo */
        $repo = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_SERVICE);
        $services = $repo->getAll();

        foreach ($services as $service) {
            if (in_array($service->getId(), $ids)) {
                if ($secondary && $service->isSecondary()) {
                    $ret[$service->getId()] = $service;
                } elseif ($primary && !$service->isSecondary()) {
                    $ret[$service->getId()] = $service;
                }
            }
        }

        return apply_filters('sln.booking_builder.getServices',$ret);
    }

    public function getPrimaryServices()
    {
        return $this->getServices(true, false);
    }

    public function getSecondaryServices()
    {
        return $this->getServices(false, true);
    }

    public function getTotal()
    {
        $ret = 0;
        foreach ($this->getServices() as $s) {
            $attendantID = isset($this->getAttendantsIds()[$s->getId()]) ? $this->getAttendantsIds()[$s->getId()] : null;
            $price           = $s->getVariablePriceEnabled() && $s->getVariablePrice($attendantID) !== '' ? $s->getVariablePrice($attendantID) : $s->getPrice();

            $price = $price * $this->getCountService($s->getId());

            $ret = $ret + SLN_Func::filter($price, 'float');
        }

	$ret += SLN_Func::filter($this->getTips(), 'float');
        $settings = SLN_Plugin::getInstance()->getSettings();
        if($settings->get('enable_booking_tax_calculation') && 'inclusive' !== $settings->get('enter_tax_price')){
            $ret = $ret * (1 + floatval($settings->get('tax_value')) / 100);
        }

        $ret = apply_filters('sln.booking_builder.getTotal', $ret, $this);

        return SLN_Func::filter($ret, 'float');
    }

    public function create($bookingStatus = '')
    {
        $settings             = $this->plugin->getSettings();
        $datetime             = $this->plugin->format()->datetime($this->getDateTime());
        $name                 = $this->get('firstname') . ' ' . $this->get('lastname');
        $status               = $bookingStatus ? $bookingStatus : $this->getCreateStatus();

	$args = array(
	    'post_type' => SLN_Plugin::POST_TYPE_BOOKING,
	    'post_title' => $name.' - '.$datetime,
	);

	$args = apply_filters('sln.booking_builder.create.getPostArgs', $args);

	$id = wp_insert_post($args);

        do_action('sln.booking_builder.create', $this);

	if ($status === SLN_Enum_BookingStatus::PENDING_PAYMENT && $settings->get('disable_first_pending_payment_email_to_customer')) {
            update_post_meta($id, '_'.SLN_Plugin::POST_TYPE_BOOKING.'_disable_status_change_email', 1);
	}

        foreach ($this->data as $k => $v) {
            update_post_meta($id, '_'.SLN_Plugin::POST_TYPE_BOOKING.'_'.$k, $v);
        }
        $discounts = $this->get('discounts');
        $this->clear($id);
        do_action('sln.api.booking.pre_eval', $this->getLastBooking(), $discounts);
        $lastBooking = $this->getLastBooking();
        $lastBooking->evalBookingServices();
        $lastBooking->evalTotal();
        $lastBooking->evalDuration();
        $lastBooking->setStatus($status);

        $userid = $this->getLastBooking()->getUserId();
        if ($userid) {
            $user = new WP_User($userid);
            if (array_search('administrator', $user->roles) === false && array_search(
	                'subscriber',
	                $user->roles
	            ) !== false
            ) {
                wp_update_user(
                    array(
                        'ID' => $userid,
                        'role' => SLN_Plugin::USER_ROLE_CUSTOMER,
                    )
                );
            }
        }
        $this->plugin->getBookingCache()->processBooking($lastBooking, true);

	do_action('sln.booking_builder.create.booking_created', $this->getLastBooking());
    }

    private function getCreateStatus()
    {
        $settings = $this->plugin->getSettings();

        $status = $settings->get('confirmation') ?
            SLN_Enum_BookingStatus::PENDING
            : ($settings->isPayEnabled() && $this->getTotal() > 0 ?
                SLN_Enum_BookingStatus::PENDING_PAYMENT
                : SLN_Enum_BookingStatus::CONFIRMED);

	return apply_filters('sln.booking_builder.getCreateStatus', $status);
    }

    public function getEndsAt()
    {
        $endsAt = clone $this->getDateTime();
        $endsAt->modify("+".SLN_Func::getMinutesFromDuration($this->getDuration())."minutes");

        return $endsAt;
    }

    public function getDuration()
    {
        $i = $this->getServicesDurationMinutes();
        $str = SLN_Func::convertToHoursMins($i);
        return $str;
    }

    public function getServicesDurationMinutes()
    {
        $h = 0;
        $i = 0;
		$max = 0;
        foreach ($this->getServices() as $s) {
	        $d = $s->getTotalDuration();
			$dInMinutes = intval($d->format('H')) * 60 + intval($d->format('i'));
			if ($s->isExecutionParalleled()) {
				if ($dInMinutes > $max) {
					$max = $dInMinutes;
				}
			} else {
				$i += $dInMinutes;
			}
        }
		$i += $max;

        return $i;
    }

    /**
     * @return SLN_Wrapper_Booking_Services
     */
    public function getBookingServices()
    {
        return SLN_Wrapper_Booking_Services::build(
            $this->getAttendantsIds(),
            $this->getDateTime(),
            0,
            $this->getCountServices()
        );
    }

    public function isValid()
    {
	SLN_Plugin::addLog('SLN booking date/time: ' . $this->getDate() . ' ' . $this->getTime());
	SLN_Plugin::addLog('SLN booking services: ' . print_r($this->data['services'], true));
	SLN_Plugin::addLog('SLN booking settings: ' . print_r(array(
	    'attendant_enabled' => $this->plugin->getSettings()->isAttendantsEnabled(),
	), true));

        $ah = SLN_Plugin::getInstance()->getAvailabilityHelper();
        if ( ! $ah->isValidTime($this->getDateTime())) {
            return false;
        }
        if ($this->data['services']) {
            $bookingServices = SLN_Wrapper_Booking_Services::build($this->data['services'], $this->getDateTime(), 0, $this->getCountServices());
            foreach ($bookingServices->getItems() as $bookingService) {

            SLN_Plugin::addLog('SLN booking service id: ' . print_r($bookingService->getService()->getId(), true));
            SLN_Plugin::addLog('SLN booking service attendant enabled: ' . print_r($bookingService->getService()->isAttendantsEnabled(), true));
        if($attendant = $bookingService->getAttendant()){
            if(!is_array($attendant)){
                SLN_Plugin::addLog('SLN booking service attendant id: ' . print_r($attendant ? $attendant->getId() : '', true));
            }else{
                SLN_Plugin::addLog('SLN booking service attendant ids: '. print_r(SLN_Wrapper_Attendant::getArrayAttendantsValue('getId', $attendant), true));
            }
        }

		if ($res = $ah->validateBookingService($bookingService)) {
                    return false;
                }
                if ($bookingService->getAttendant() && !is_array($bookingService->getAttendant()) && $res = $ah->validateBookingAttendant($bookingService)) {
                    return false;
                }elseif(is_array($bookingService->getAttendant()) && $ah->validateBookingAttendants($bookingService)){
                    return false;
                }
                if ($this->plugin->getSettings()->isAttendantsEnabled() && !$bookingService->getAttendant() && $bookingService->getService()->isAttendantsEnabled()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getTips()
    {
	return $this->get('tips');
    }

    public function addTips($tips)
    {
	return $this->set('tips', SLN_Func::filter($this->getTips(), 'float') + SLN_Func::filter($tips, 'float'));
    }

    public function setCustomerTimezone($timezone)
    {
        $this->data['customer_timezone'] = $timezone;

        return $this;
    }

    public function getDateTimeCustomerTimezone()
    {
	return (new SLN_DateTime($this->getDate().' '.$this->getTime()))->setTimezone(new DateTimeZone($this->get('customer_timezone')));
    }

    public function getCountService($serviceID)
    {
        return isset($this->data['service_count'][$serviceID]) ? $this->data['service_count'][$serviceID] : 1;
    }

    public function addCountService($serviceID, $countService)
    {
        $serviceCount = $this->get('service_count') && is_array($this->get('service_count')) ? $this->get('service_count') : array();
        $serviceCount[$serviceID] = $countService;

        $this->set('service_count', $serviceCount);
    }

    public function removeCountService($serviceID)
    {
        $serviceCount = $this->get('service_count') && is_array($this->get('service_count')) ? $this->get('service_count') : array();

        if (isset($serviceCount[$serviceID])) {
            unset($serviceCount[$serviceID]);
        }

        $this->set('service_count', $serviceCount);
    }

    public function getCountServices()
    {
        return isset($this->data['service_count']) ? $this->data['service_count'] : array();
    }

    public function getTaxFromTotal(){
        $settings = $this->plugin->getSettings();
        if(!$settings->get('enable_booking_tax_calculation')){
            return 0;
        }
        $priceExcludeTax = $this->getTotal() / (1 + floatval($settings->get('tax_value')) / 100);
        return $priceExcludeTax * (floatval($settings->get('tax_value')) / 100);
    }

}
