<?php

class SLN_Action_Ajax_CalcBookingTotal extends SLN_Action_Ajax_Abstract
{
    public function execute()  {

	$booking  = $this->plugin->createFromPost($_POST['post_ID']);
	$settings = $this->plugin->getSettings();

	$startsAt = $this->getStartsAt($_POST['_sln_booking_date'], $_POST['_sln_booking_time']);
	$services = $this->processServicesSubmission($_POST['_sln_booking']);

	$bookingServices = SLN_Wrapper_Booking_Services::build(
	    apply_filters('sln.calc_booking_total.get_services', $services, SLN_Wrapper_Booking_Services::build($services, $startsAt, 0, $booking->getCountServices())),
	    $startsAt,
        0,
        $booking->getCountServices()
	);


	$total    = $this->getTotal($bookingServices, $booking);
        $deposit  = $_POST['_sln_booking_deposit'] > 0.0 ? $_POST['_sln_booking_deposit'] : $this->getDeposit($total, $settings, $booking);
        $duration = $this->getDuration($bookingServices);

        return array(
	    'total'	=> round($total, 2),
	    'deposit'	=> round($deposit, 2),
	    'duration'	=> $duration,
	    'discounts'	=> apply_filters('sln.calc_booking_total.get_discounts_html', ''),
	    'services'  => $this->getServicesPrices($bookingServices),
	);
    }

    protected function getTotal($bookingServices, $booking) {

	    $total = 0;

        foreach ($bookingServices->getItems() as $bookingService) {
            $price   = $bookingService->getPrice();
            $total += $price;
        }
        $settings = SLN_Plugin::getInstance()->getSettings();
        if($settings->get('enable_booking_tax_calculation') && 'inclusive' !== $settings->get('enter_tax_price')){
            $total = $totla * (1 + floatval($settings->get('tax_value')) / 100);
        }

	    $total += $booking->getTips();

	return $total;
    }

    protected function getDeposit($total, $settings, $booking) {

        $depositAmount = $settings->getPaymentDepositAmount();

        if ($settings->isPaymentDepositFixedAmount()) {
	    $deposit = min($total, $depositAmount);
        }
        else {
            $deposit = ($total / 100) * $depositAmount;
        }

        return $deposit;
    }

    protected function getDuration($bookingServices) {

	$h = 0;
	$i = 0;

	foreach ($bookingServices->getItems() as $bookingService) {
	    $d = $bookingService->getTotalDuration();
	    $h = $h + intval($d->format('H'));
	    $i = $i + intval($d->format('i'));
	}

	$i += $h * 60;

	return SLN_Func::convertToHoursMins($i);
    }


    protected function processServicesSubmission($data) {

        $services     = array();
        $services_ids = array_map('intval',$data['service']);

	foreach ($services_ids as $serviceId) {
        if(0 == $serviceId){
            continue;
        }
	    $duration      = SLN_Func::convertToHoursMins($data['duration'][$serviceId]);
            $breakDuration = SLN_Func::convertToHoursMins($data['break_duration'][$serviceId]);

            $attendant = isset($data['attendants']) ? $data['attendants'][$serviceId] : (isset($data['attendant']) ? $data['attendant'] : null);
            $service = $this->plugin->getInstance()->createService($serviceId);
            $service = apply_filters('sln.booking_services.buildService', $service);
            if(0 == $attendant && $this->plugin->getSettings()->isAttendantsEnabled() && $service->isAttendantsEnabled()){
                continue;
            }
            $services[$serviceId] = array(
                'service' => $serviceId,
                'attendant' => $attendant,
                'duration' => $duration,
                'break_duration' => $breakDuration,
            );
	}

        return $services;
    }

    protected function getStartsAt( $date, $time, $timezone = '' )
    {
	if($timezone) {
	    return new SLN_DateTime(SLN_Func::filter($date, 'date').' '.SLN_Func::filter($time, 'time'), new DateTimeZone($timezone) );
	} else {
	    return new SLN_DateTime(SLN_Func::filter($date, 'date').' '.SLN_Func::filter($time, 'time'));
	}
    }

    protected function getServicesPrices($bookingServices)
    {
	$prices = array();

	foreach ($bookingServices->getItems() as $bookingService) {
	    $service			= $bookingService->getService();
	    $prices[$service->getId()]	= strip_tags($service->getName() . ' (' . $this->plugin->format()->money($bookingService->getPrice(), true, true, false, true) . ') - ' . $bookingService->getDuration()->format('H:i'));
	}

	return $prices;
    }

}
