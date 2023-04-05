<?php

use Salon\Util\Date;

class SLN_Helper_Intervals
{
    /** @var  SLN_Helper_Availability */
    protected $availabilityHelper;
    protected $initialDate;
    protected $suggestedDate;

    protected $times;
    protected $years;
    protected $months;
    protected $days;
    protected $dates;
    protected $fullDays  = array();
    protected $workTimes = array();

    public function __construct(SLN_Helper_Availability $availabilityHelper)
    {
        $this->availabilityHelper = $availabilityHelper;
    }

    public function setDatetime(DateTime $date, $duration = null)
    {
        $this->initialDate = $this->bindInitialDate($date);
        $ah                = $this->availabilityHelper;
        $times             = $ah->getCachedTimes(Date::create($date), $duration);
        $interval          = $ah->getHoursBeforeHelper();
        $to                = $interval->getToDate();
        $clone             = clone $date;
        while (empty($times) && $date <= $to) {
            $this->fullDays[] = clone $date;
            $date->modify('+1 days');
            $times = $ah->getCachedTimes( Date::create($date), $duration);
        }
        if (empty($times)) {
            $date = $clone;
            $from = $interval->getFromDate();
            while (empty($times) && $date >= $from) {
                $this->fullDays[] = clone $date;
                $date->modify('-1 days');
                $times = $ah->getCachedTimes(Date::create($date), $duration);
            }
        }
        $this->times   = $times;
        $suggestedTime = $date->format('H:i');
        $i             = SLN_Plugin::getInstance()->getSettings()->getInterval();
        $timeout = 0;
        if(!isset($times[$suggestedTime])){
            $date->setTime(0,0);
            $suggestedTime = $date->format('H:i');
            while ($timeout < 86400 && !isset($times[$suggestedTime]) && $date <= $to ) {
                $date->modify("+$i minutes");
                $suggestedTime = $date->format('H:i');
                $timeout++;
            }
        }
        $this->suggestedDate = $date;
        $this->bindDates($ah->getCachedDays());
        ksort($this->times);
        ksort($this->years);
        ksort($this->days);
        ksort($this->months);

        $this->workTimes = $ah->getWorkTimes(Date::create($date));
    }

    public function bindInitialDate($date)
    {
        $from = $this->availabilityHelper->getHoursBeforeHelper()->getFromDate();
        if ($date < $from) {
            $date = $from;
        }

        return $date;
    }

    private function bindDates($dates)
    {
        $this->years  = array();
        $this->months = array();
        $this->days   = array();
        $this->dates  = array();
        $checkDay     = $this->suggestedDate->format('Y-m-');
        $checkMonth   = $this->suggestedDate->format('Y-');
        foreach ($dates as $date) {
            list($year, $month, $day) = explode('-', $date->getDateTime()->format('Y-m-d'));
            $this->years[$year] = $date;
            if (strpos($date->getDateTime()->format('Y-m-d'), $checkMonth) === 0) {
                $this->months[$month] = $date;
            }
            if (strpos($date->getDateTime()->format('Y-m-d'), $checkDay) === 0) {
                $this->days[$day] = $date;
            }
            $this->dates[] = $date;
        }
        ksort($this->years);
        ksort($this->months);
        ksort($this->days);
    }

    public function toArray($timezone = '')
    {
        $f = SLN_plugin::getInstance()->format();

        $suggestedDate = $timezone ? $this->suggestedDate->setTimezone(new DateTimeZone($timezone)) : $this->suggestedDate;

        $times = array();
        $currentTime = (new DateTime())->modify(SLN_Plugin::getInstance()->getSettings()->getHoursBeforeFrom());
        $currentTime->setTimezone(wp_timezone());
        //for SLB_API_Mobile purposes
        $customTimeFormat = $_GET['time_format'] ?? false;

        foreach ($this->times as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            if($currentTime <= $v){
                $times[$v->format($customTimeFormat ?: 'H:i')] = $v->format($customTimeFormat ?: 'H:i');
            }
        }

        $dates = array();

        foreach ($this->dates as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $dates[] = $v->format('Y-m-d');
        }

        $years = array();

        foreach ($this->years as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $years[$v->format('Y')] = $v->format('Y');
        }

        $months = SLN_Func::getMonths();
        $monthsList = array();

        foreach ($this->months as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $monthsList[$v->format('m')] = $months[intval($v->format('m'))];
        }

        $days = array();

        foreach ($this->days as $v) {
            $v = $timezone ? $v->getDateTime()->setTimezone(new DateTimeZone($timezone)) : $v->getDateTime();
            $days[$v->format('d')] = $v->format('d');
        }

        $workTimes = array();

        foreach ($this->workTimes as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $workTimes[$v->format($customTimeFormat ?: 'H:i')] = $v->format($customTimeFormat ?: 'H:i');
        }

        $fullDays = array();

        foreach ($this->fullDays as $v) {
            $v = $timezone ? $v->setTimezone(new DateTimeZone($timezone)) : $v;
            $fullDays[] = $v->format('Y-m-d');
        }

        return array(
            'years'          => $years,
            'months'         => $monthsList,
            'days'           => $days,
            'times'          => $times,
            'dates'          => $dates,
            'workTimes'      => $workTimes,
            'fullDays'       => $fullDays,
            'suggestedDay'   => $suggestedDate->format('d'),
            'suggestedMonth' => $suggestedDate->format('m'),
            'suggestedYear'  => $suggestedDate->format('Y'),
            'suggestedDate'  => $f->date($suggestedDate),
            'suggestedTime'  => $f->time($suggestedDate, $customTimeFormat),
            'universalSuggestedDate' => $suggestedDate->format('Y-m-d'),
        );
    }

    /**
     * @return mixed
     */
    public function getInitialDate()
    {
        return $this->initialDate;
    }

    /**
     * @return mixed
     */
    public function getSuggestedDate()
    {
        return $this->suggestedDate;
    }

    /**
     * @return mixed
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @return mixed
     */
    public function getYears()
    {
        return $this->years;
    }

    /**
     * @return mixed
     */
    public function getMonths()
    {
        return $this->months;
    }

    /**
     * @return mixed
     */
    public function getDays()
    {
        return $this->days;
    }
    public function getDates(){
        return $this->dates;
    }

    public function getFullDays(){
        return array_merge(array_unique($this->fullDays), array_map(function($date) { return new DateTime($date); }, SLN_Plugin::getInstance()->getBookingCache()->getFullDays()));
    }

    public function getWorkTimes(){
        return $this->workTimes;
    }
}
