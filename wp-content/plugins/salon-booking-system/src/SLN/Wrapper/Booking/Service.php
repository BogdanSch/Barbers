<?php


final class SLN_Wrapper_Booking_Service
{
    private $data;

    /**
     * SLN_Wrapper_Booking_Service constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $hasAttendant = isset($data['attendant']) && !empty($data['attendant']);
        $data['break_duration'] = isset($data['break_duration']) ? $data['break_duration'] : '00:00';
        $this->data = array();

        if(!empty($data['service'])) $this->data['service'] = SLN_Plugin::getInstance()->createService($data['service']);

        $this->data['attendant'] = $hasAttendant ?
                apply_filters('sln.booking_services.buildAttendant', SLN_Plugin::getInstance()->createAttendant($data['attendant']))
                :
                false;

        if(!empty($data['start_date']) && !empty($data['start_time'])) $this->data['starts_at'] = new SLN_DateTime(
                SLN_Func::filter($data['start_date'], 'date').' '.SLN_Func::filter($data['start_time'], 'time'),SLN_TimeFunc::getWpTimezone()
        );
        if(!empty($data['duration'])) $this->data['duration'] = new SLN_DateTime('1970-01-01 '.SLN_Func::filter($data['duration'], 'time'));
        if(!empty($data['break_duration'])) $this->data['break_duration'] = new SLN_DateTime('1970-01-01 '.SLN_Func::filter($data['break_duration'], 'time'));
        $this->data['break_duration_data'] = !empty($data['break_duration_data']) ? $data['break_duration_data'] : array('from' => 0, 'to' => SLN_Func::getMinutesFromDuration($data['break_duration']));
        if(!empty($data['duration']) && !empty($data['break_duration'])) $this->data['total_duration'] = new SLN_DateTime('1970-01-01 '.SLN_Func::convertToHoursMins(SLN_Func::getMinutesFromDuration($data['duration']) + SLN_Func::getMinutesFromDuration($data['break_duration'])));

	$this->data['price'] = null;

	if(!empty($data['price'])) $this->data['price'] = $data['price'];
        if(!empty($data['exec_order'])) $this->data['exec_order'] = $data['exec_order'];

        $this->data['service'] = apply_filters('sln.booking_services.buildService', $this->data['service']);

		if (!empty($data['parallel_exec'])) {
			$this->data['parallel_exec'] = $data['parallel_exec'];
		}

        if (!empty($data['count'])) {
			$this->data['count'] = $data['count'];
		}
    }

    /**
     * @param SLN_Wrapper_AttendantInterface|false $attendant
     */
    public function setAttendant($attendant = false) {
        $this->data['attendant'] = $attendant;
    }

    /**
     * @return SLN_DateTime
     */
    public function getDuration()
    {
        return $this->data['duration'];
    }

    /**
     * @return SLN_DateTime
     */
    public function getBreakDuration()
    {
        return $this->data['break_duration'];
    }

    /**
     * @return array
     */
    public function getBreakDurationData()
    {
        return $this->data['break_duration_data'];
    }

    /**
     * @return SLN_DateTime
     */
    public function getTotalDuration()
    {
        return $this->data['total_duration'];
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return floatval($this->data['price']);
    }

    public function setPrice($price)
    {
        $this->data['price'] = $price;
    }

    /**
     * @return SLN_Wrapper_ServiceInterface
     */
    public function getService()
    {
        return $this->data['service'];
    }

    /**
     * @return SLN_Wrapper_AttendantInterface|false
     */
    public function getAttendant()
    {
        return $this->data['attendant'];
    }

    /**
     * @return SLN_DateTime
     */
    public function getStartsAt()
    {
        return $this->data['starts_at'];
    }

    /**
     * @return SLN_DateTime
     */
    public function getEndsAt()
    {
        $minutes = SLN_Func::getMinutesFromDuration($this->getTotalDuration());
        $endsAt = clone $this->getStartsAt();
        $endsAt->modify('+'.$minutes.' minutes');

        return $endsAt;
    }

	public function getParallelExec() {
		return !empty($this->data['parallel_exec']) ? $this->data['parallel_exec'] : false;
	}

    public function getCountServices() {
		return !empty($this->data['count']) ? $this->data['count'] : 1;
	}

    private function processBreakInfo() {
        if (isset($this->breakProcessed)) {
            return;
        }
        $minutes      = SLN_Func::getMinutesFromDuration($this->getDuration());
        $breakMinutes = SLN_Func::getMinutesFromDuration($this->getBreakDuration());

        if ($breakMinutes) {
            $busyTime = $minutes;
            $busyPart = (int) ceil($busyTime / 2);

            $breakMinutesData = $this->getBreakDurationData();

            $breakStartsAt = clone $this->getStartsAt();
            $breakStartsAt->modify('+'.$breakMinutesData['from'].' minutes');

            $breakEndsAt = clone $this->getStartsAt();
            $breakEndsAt->modify('+'.$breakMinutesData['to'].' minutes');

            $bookingOffsetEnabled = SLN_Plugin::getInstance()->getSettings()->get('reservation_interval_enabled');
            if ($bookingOffsetEnabled) {
                $bookingOffset = SLN_Plugin::getInstance()->getSettings()->get('minutes_between_reservation');
            } else {
                $bookingOffset = 0;
            }

            $breakWithOffsetStartsAt = clone $breakStartsAt;
            $breakWithOffsetStartsAt->modify('+'.$bookingOffset.' minutes');

            $breakWithOffsetEndsAt = clone $breakEndsAt;
            $breakWithOffsetEndsAt->modify('-'.$bookingOffset.' minutes');
        } else {
            $breakStartsAt           = clone $this->getStartsAt();
            $breakWithOffsetStartsAt = clone $this->getStartsAt();
            $breakEndsAt             = clone $this->getStartsAt();
            $breakWithOffsetEndsAt   = clone $this->getStartsAt();
        }
        $this->breakStartsAt = $breakStartsAt;
        $this->breakEndsAt = $breakEndsAt;
        $this->breakWithOffsetStartsAt = $breakWithOffsetStartsAt;
        $this->breakWithOffsetEndsAt = $breakWithOffsetEndsAt;
        $this->breakProcessed = true;
    }

    /**
     * @return SLN_DateTime
     */
    public function getBreakStartsAt()
    {
        $this->processBreakInfo();

        return $this->breakStartsAt;
    }

    /**
     * @return SLN_DateTime
     */
    public function getBreakEndsAt()
    {
        $this->processBreakInfo();

        return $this->breakEndsAt;
    }

    /**
     * @return SLN_DateTime
     */
    public function getBreakWithOffsetStartsAt()
    {
        $this->processBreakInfo();

        return $this->breakWithOffsetStartsAt;
    }

    /**
     * @return SLN_DateTime
     */
    public function getBreakWithOffsetEndsAt()
    {
        $this->processBreakInfo();

        return $this->breakWithOffsetEndsAt;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if(is_object($this->data['attendant'])){
            $attendant = $this->data['attendant']->getId();
        }elseif(isset($this->data['attendant']) && $this->data['service']->isMultipleAttendantsForServiceEnabled()){
            $attendant = SLN_Wrapper_Attendant::getArrayAttendantsValue('getId', $this->data['attendant']);
        }else{
            $attendant = $this->data['attendant'];
        }
        return array(
            'attendant' => $attendant,
            'service' => $this->data['service']->getId(),
            'is_secondary' => $this->data['service']->isSecondary() ? 1 : 0,
            'duration' => $this->data['duration']->format('H:i'),
            'break_duration' => $this->data['break_duration']->format('H:i'),
            'break_duration_data' => $this->data['break_duration_data'],
            'start_date' => $this->data['starts_at']->format('Y-m-d'),
            'start_time' => $this->data['starts_at']->format('H:i'),
            'price' => floatval($this->data['price']),
            'exec_order' => intval($this->data['exec_order']),
	        'is_parallel_exec' => $this->getParallelExec() ? 1 : 0,
            'count' => $this->getCountServices(),
        );
    }

    public function __toString()
    {
        return $this->getService()->__toString();
    }
}
