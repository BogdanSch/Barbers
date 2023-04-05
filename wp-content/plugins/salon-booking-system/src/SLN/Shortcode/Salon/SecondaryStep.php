<?php

class SLN_Shortcode_Salon_SecondaryStep extends SLN_Shortcode_Salon_Step
{

    protected function dispatchForm()
    {
        $bb     = $this->getPlugin()->getBookingBuilder();
        $values = isset($_POST['sln']) && isset($_POST['sln']['services']) && is_array($_POST['sln']['services'])  ? $_POST['sln']['services'] : array();
        $countService = isset($_REQUEST['sln']) && isset($_REQUEST['sln']['service_count']) && is_array($_REQUEST['sln']['service_count'])  ? $_REQUEST['sln']['service_count'] : array();
        foreach ($this->getServices() as $service) {
            if (isset($values) && isset($values[$service->getId()])) {
                $bb->addService($service);
            } else {
                $bb->removeService($service);
            }
            if (isset($countService) && isset($countService[$service->getId()])) {
                $bb->addCountService($service->getId(), $countService[$service->getId()]);
            } else {
                $bb->removeCountService($service->getId());
            }
        }

        $bb->save();
        if(isset($_GET['sln'])) {
            return false;
        } elseif( SLN_Plugin::getInstance()->getSettings()->get( 'is_secondary_services_selection_required' ) ){
            $secondaryCount = SLN_Plugin::getInstance()->getSettings()->get( 'secondary_services_count' );
            if( count( $values ) != $secondaryCount ){
                $this->addError( esc_html(__('You need to choose ', 'salon-booking-system'). sprintf( _n('%d service', '%d services', $secondaryCount, 'salon-booking-system' ), $secondaryCount ) ) );
                return false;
            }
        }

	if ( ! $this->validateMinimumOrderAmount() ) {
	    return false;
	}

        return true;
    }

    /**
     * @return SLN_Wrapper_Service[]
     */
    public function getServices()
    {
        if ( ! isset($this->services)) {
            /** @var SLN_Repository_ServiceRepository $repo */
            $repo     = $this->getPlugin()->getRepository(SLN_Plugin::POST_TYPE_SERVICE);
            $services = $repo->getAllSecondary();

            $bb = $this->getPlugin()->getBookingBuilder();
            $ah = $this->getPlugin()->getAvailabilityHelper();
            $ah->setDate($bb->getDateTime());
            $bookingServices = $bb->getBookingServices();
            foreach ($services as $k => $service) {
                $errs = $ah->validateServiceFromOrder($service, $bookingServices);
                if ( ! empty($errs)) {
                    unset($services[$k]);
                }
            }

	    $services = array_filter($services, function ($service) {
		return !$service->isHideOnFrontend();
	    });

	    $this->services = $repo->sortByExecAndTitleDESC($services);
            $this->services = apply_filters('sln.shortcode.salon.SecondaryStep.getServices', $this->services);
        }

        return $this->services;
    }

    public function getTotal()
    {

    }

    public function isValid()
    {
        $tmp = $this->getServices();

        if (!empty($tmp)) {
            return parent::isValid();
        }
        else {
            parent::isValid();
            return true;
        }
    }
}
