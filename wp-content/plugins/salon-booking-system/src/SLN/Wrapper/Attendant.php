<?php

class SLN_Wrapper_Attendant extends SLN_Wrapper_Abstract implements SLN_Wrapper_AttendantInterface
{
    const _CLASS = 'SLN_Wrapper_Attendant';
    private $availabilityItems;
    private $holidayItems;

    public function getPostType()
    {
        return SLN_Plugin::POST_TYPE_ATTENDANT;
    }

    function getNotAvailableOn($key)
    {
        $post_id = $this->getId();
        $ret = apply_filters(
            'sln_attendant_notav_'.$key,
            get_post_meta($post_id, '_sln_attendant_notav_'.$key, true)
        );
        $ret = empty($ret) ? false : ($ret ? true : false);

        return $ret;
    }

    function getEmail()
    {
        return $this->getMeta('email');
    }

    function getPhone()
    {
        return $this->getMeta('phone');
    }

    function getPosOrder()
    {
        $ret = $this->getMeta('order');
        $ret     = empty($ret) ? 0 : $ret;

        return $ret;
    }

    function isNotAvailableOnDate(SLN_DateTime $date)
    {
        return !($this->getAvailabilityItems()->isValidDatetime($date) && $this->getHolidayItems()->isValidDatetime($date));
    }

    function isNotAvailableOnDateDuration(SLN_DateTime $date, DateTime $duration)
    {
        return !($this->getAvailabilityItems()->isValidDatetimeDuration($date, $duration) &&
                $this->getHolidayItems()->isValidDatetimeDuration($date, $duration));
    }

    /**
     * @return SLN_Helper_AvailabilityItems
     */
    public function getAvailabilityItems()
    {
        if (!isset($this->availabilityItems)) {
            $this->availabilityItems = new SLN_Helper_AvailabilityItems($this->getMeta('availabilities'));
        }
        return $this->availabilityItems;
    }

    /**
     * @return SLN_Helper_HolidayItems
     */
    public function getHolidayItems()
    {
        if (!isset($this->holidayItems)) {
            $holidays       = $this->getMeta('holidays') ?: array();
            $daily_holidays = $this->getMeta('holidays_daily') ?: array();
            $this->holidayItems = new SLN_Helper_HolidayItems(array_merge($holidays, $daily_holidays));
        }
        return $this->holidayItems;
    }

    public function getNewHolidayItems()
    {
        $holidays       = $this->getMeta('holidays') ?: array();
        $holiday_items = new SLN_Helper_HolidayItems($holidays);
        return $holiday_items;
    }

    public function getNotAvailableString()
    {
        return '';
    }

    public function getServicesIds()
    {
        $ret = $this->getMeta('services');
        if (is_array($ret)) {
            $ret = array_unique($ret);
        }

        return empty($ret) ? array() : $ret;
    }

    public function getServices()
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

    public function hasService(SLN_Wrapper_ServiceInterface $service)
    {
        return in_array($service->getId(), $this->getServicesIds());
    }

    public function hasServices($services)
    {
        if ($this->hasAllServices()) {
            return true;
        }
        /** @var SLN_Wrapper_ServiceInterface $service */
        foreach ($services as $service) {
            if (!$this->hasService($service)) {
                return false;
            }
        }
        return true;
    }

    public static function getArrayAttendantsValue($method, $attendants){
        $ret = array();
        foreach($attendants as $attendant){
            $ret[] = $attendant->$method();
        }

        return $ret;
    }

    public static function implodeArrayAttendantsName($separator, $attendants){
        return implode($separator, self::getArrayAttendantsValue('getName', $attendants));
    }

    public function hasAllServices()
    {
        //an assistant without services is an assistant available for all services
        return $this->getServicesIds() ? false : true;
    }

    public function getGoogleCalendar()
    {
        return $this->getMeta('google_calendar');
    }

    public function getName()
    {
        return $this->getTitle();
    }

    public function getContent()
    {
        $object = SLN_Helper_Multilingual::isMultilingual()  ? $this->translationObject : $this->object;
        if ($object) {
            if(isset($object->post_excerpt))
            return $object->post_excerpt;
        }
    }

    public function canMultipleCustomers(){
        return $this->getMeta('multiple_customers');
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getIsStaffMemberAssignedToBookingsOnly() {
	return $this->getMeta('limit_staff_member_to_assigned_bookings_only');
    }

    public function isDisplayPhoneInsideBookingNotification() {
	return $this->getMeta('display_phone_inside_booking_notification');
    }

    public function getSmsPrefix() {
	return $this->getMeta('sms_prefix') ? $this->getMeta('sms_prefix') : SLN_Plugin::getInstance()->getSettings()->get('sms_prefix');
    }
}
