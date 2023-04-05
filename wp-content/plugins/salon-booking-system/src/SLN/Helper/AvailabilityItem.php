<?php

use Salon\Util\Date;
use Salon\Util\DateInterval;
use Salon\Util\Time;
use Salon\Util\TimeInterval;

class SLN_Helper_AvailabilityItem
{
    private $data;
    /** @var TimeInterval[] */
    private $times = array();
	/** @var DateInterval */
    private $period;


	function __construct( $data ) {
		$this->data = $data;
		if ( $data ) {
			for ( $i = 0; $i <= 1; $i ++ ) {
                if(!isset($data['from'][ $i ],$data['to'][ $i ])) continue;
				if ( $data['from'][ $i ] != '00:00' ) {
					$this->times[] = new TimeInterval(
						new Time( $data['from'][ $i ] ),
						new Time( $data['to'][ $i ] )
					);
				}
			}
			$from         = isset( $data['from_date'] ) ? new Date( $data['from_date'] ) : null;
			$to           = isset( $data['to_date'] ) ? new Date( $data['to_date'] ) : null;
			$this->period = new DateInterval( $from, $to );
		}else{
			$this->period = new DateInterval();
		}
		if ( empty( $this->times ) ) {
			$this->times[] = new TimeInterval(
				new Time( '00:00' ),
				new Time( '24:00' )
			);
		}
	}

    /**
     * @param $date
     * @return bool
     */
    public function isValidDate(Date $date)
    {
        return $this->isValidDayOfPeriod($date) && (!$this->isSelectSpecificDates() ? $this->isValidDayOfWeek($date) : $this->isValidSpecificDates($date));
    }

    public function isAlwaysOn()
    {
        return $this->period->isAlways();
    }

    /**
     * @param $date
     * @return bool
     */
    public function isValidDayOfPeriod(Date $date)
    {
    	return $this->period->containsDate($date);
    }

    /**
     * @param $date
     * @return bool
     */
    private function isValidDayOfWeek(Date $date)
    {
        return isset($this->data['days']) && isset( $this->data['days'][ $date->getWeekday() + 1 ] );
    }

    /**
     * @param Time $time
     * @return bool
     */
    public function isValidTime(Time $time)
    {
        $endWorkTime = $this->data['to'][array_key_last($this->data['to'])];
        foreach ($this->times as $t) {
            if ($t->containsTime($time) && $time->toString() !== $endWorkTime ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param TimeInterval $interval
     * @return bool
     */
    public function isValidTimeInterval(TimeInterval $interval)
    {
        foreach ($this->times as $t) {
            if ($t->containsInterval($interval)) {
                return true;
            }
        }
        return false;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $ret     = array();
        $allDays = count($ret) == 7;

        if ( ! $this->isSelectSpecificDates() ) {
            $days = SLN_Func::getDays();
            $ret  = array();
            if (isset($this->data['days'])) {
                foreach ($this->data['days'] as $d => $v) {
                    $ret[] = $days[$d];
                }
            }
            $allDays = count($ret) == 7;
            $ret     = $allDays ? null : implode('-', $ret);
        } else {
            $ret  = isset($this->data['specific_dates']) ? implode(', ', array_map(function ($item) { return SLN_Plugin::getInstance()->format()->date($item); }, explode(',', $this->data['specific_dates']))) : '';
        }

        $format  = SLN_Plugin::getInstance()->format();
	    foreach ( $this->times as $t ) {
		    if ( ! ( $t->isAlways() || $t->isNever() ) ) {
			    $ret .= sprintf(
				    ' %s/%s',
				    $format->time( $t->getFrom() ),
				    $format->time( $t->getTo() )
			    );
		    }
	    }
        if (empty($ret)) {
            $ret = __('Always', 'salon-booking-system');
        }
        if ($allDays) {
            $ret = __('All days', 'salon-booking-system').$ret;
        }

        return $ret;
    }

    /**
     * @return TimeInterval[]
     */
    public function getTimes(){
        return $this->times;
    }

    public function isSelectSpecificDates() {
        return defined("SLN_VERSION_PAY") && isset($this->data['select_specific_dates']) && $this->data['select_specific_dates'];
    }

    public function isValidSpecificDates(Date $date) {
        $specificDates = isset($this->data['specific_dates']) ? explode(',', $this->data['specific_dates']) : array();
        return in_array($date->getDateTime()->format('Y-m-d'), $specificDates);
    }
}
