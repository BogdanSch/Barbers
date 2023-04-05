<?php

final class SLN_Wrapper_Booking_Services {

	private $items = array();

	/**
	 * SLN_Wrapper_Booking_Services constructor.
	 *
	 * @param $data
	 */
	public function __construct( $data ) {
		if(!empty($data)){
			foreach ($data as $item) {
				$this->items[] = new SLN_Wrapper_Booking_Service($item);
			}
		}
	}

	/**
	 * @return SLN_Wrapper_Booking_Service[]
	 */
	public function getItems() {
		return empty($this->items) ? array() : $this->items;
	}

	/**
	 * @return int
	 */
	public function getCount() {
		return count($this->getItems());
	}

	/**
	 * @return null|SLN_Wrapper_Booking_Service
	 */
	public function getFirstItem() {
		$items = $this->getItems();
		return empty($items) ? null : reset($items);
	}

	/**
	 * @param int $serviceId
	 *
	 * @return false|SLN_Wrapper_Booking_Service
	 */
	public function findByService($serviceId) {
		foreach($this->getItems() as $bookingService) {
			if ($serviceId == $bookingService->getService()->getId()) {
				return $bookingService;
			}
		}
		return false;
	}

	/**
	 * @param SLN_Wrapper_Booking_Service $bookingService
	 *
	 * @return bool|int
	 */
	public function getPosInQueue(SLN_Wrapper_Booking_Service $bookingService) {
		$pos = array_search($bookingService, $this->items);

		return ($pos === false ? $pos : $pos + 1);
	}

	public function isLast(SLN_Wrapper_Booking_Service $bookingService) {
		return count($this->items) && $this->items[count($this->items) - 1] === $bookingService;
	}

	public function toArrayRecursive() {
		$ret = array();
		if(!empty($this->items)){
			foreach ($this->items as $item) {
				/** @var SLN_Wrapper_Booking_Service $item */
				$ret[] = $item->toArray();
			}
		}

		return $ret;
	}

	/**
	 * @param array $data   array($service_id => $attendant_id) or array($service_id => array('attendant' => $attendant_id, 'price' => float, 'duration' => 'H:i' ))
	 * @param SLN_DateTime $startsAt
	 * @param int $offset   minutes
	 *
	 * @return SLN_Wrapper_Booking_Services
	 */
	public static function build($data, SLN_DateTime $startsAt, $offset = 0, $serviceCount = array()) {
		$startsAtClone = clone $startsAt;
		$services = array();
		foreach($data as $i => $item) {

            $sId      = null;
			$atId     = null;
			$price    = null;
			$duration = null;
			$break    = null;
			$break_duration_data = null;

			if (is_array($item) && array_intersect(array_keys($item), array('service', 'attendant', 'price', 'duration', 'break_duration', 'break_duration_data'))) {
                if (isset($item['service'])) {
                    $sId = intval($item['service']);
                }
				if (isset($item['attendant'])) {
					if(!is_array($item['attendant'])){
						$atId = intval($item['attendant']);
					}else{
						$atId = array_map('intval', $item['attendant']);
					}
				}
				if (isset($item['price'])) {
					$price = floatval($item['price']);
				}
				if (isset($item['duration'])) {
					$duration = $item['duration'];
				}
				if (isset($item['break_duration'])) {
					$break = $item['break_duration'];
				}
				if (isset($item['break_duration_data'])) {
					$break_duration_data = $item['break_duration_data'];
				}
			} else {
                $sId  = intval($i);
				if(!is_array($item)){
					$atId = intval($item);
				}else{
					$atId = array_map('intval', $item);
				}
			}

            $service = SLN_Plugin::getInstance()->createService($sId);
            $service = apply_filters('sln.booking_services.buildService', $service);

			if (is_null($price)) {
                $price = $service->getVariablePriceEnabled() && $service->getVariablePrice($atId) !== '' ? $service->getVariablePrice($atId) : $service->getPrice();
                $count = isset($serviceCount[$service->getId()]) ? $serviceCount[$service->getId()] : 1;
                $price = $price * $count;
			}

			if (empty($duration)) {
				$duration = $service->getDuration()->format('H:i');
                $ret = SLN_Func::getMinutesFromDuration($duration)*60;
                $count = isset($serviceCount[$service->getId()]) ? $serviceCount[$service->getId()] : 1;
                $duration = $ret * $count;
                $duration = (new SLN_DateTime('@'.$duration))->format('H:i');
			}

			if (empty($break)) {
				$break = $service->getBreakDuration()->format('H:i');
			}

			if (empty($break_duration_data)) {
				$break_duration_data = $service->getBreakDurationData();
			}

			$parallelExec = $service->isExecutionParalleled();

            $count = isset($serviceCount[$service->getId()]) ? $serviceCount[$service->getId()] : 1;

			$services[] = array(
				'service'	=> $sId,
				'attendant'	=> $atId,
				'start_date'	=> $parallelExec ? $startsAt->format('Y-m-d') : $startsAtClone->format('Y-m-d'),
				'start_time'	=> $parallelExec ? $startsAt->format('H:i') : $startsAtClone->format('H:i'),
				'duration'	=> $duration,
				'break_duration'   => $break,
				'break_duration_data'   => $break_duration_data,
				'price'		=> $price,
				'exec_order'	=> $service->getExecOrder(),
				'parallel_exec' => $parallelExec,
                'count' => $count,
			);

			$minutes = SLN_Func::getMinutesFromDuration($duration) + SLN_Func::getMinutesFromDuration($break) + $offset;
			$startsAtClone->modify('+'.$minutes.' minutes');
		}
		usort($services, array('SLN_Repository_ServiceRepository', 'serviceCmp'));
		$ret = new SLN_Wrapper_Booking_Services($services);

		return $ret;
	}

}
