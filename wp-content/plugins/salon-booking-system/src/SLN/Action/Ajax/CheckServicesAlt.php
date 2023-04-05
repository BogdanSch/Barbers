<?php

class SLN_Action_Ajax_CheckServicesAlt extends SLN_Action_Ajax_CheckServices
{
    protected function innerInitServices($services, $merge, $newServices)
    {
        $ret      = array();
        $builder  = $this->bb;
        $this->ah->setDate($this->bb->getDateTime());

        $mergeIds = array();
        foreach($merge as $s){
            $mergeIds[] = $s->getId();
        }
        $services      = array_merge(array_keys($services), $mergeIds);
        $settings      = $this->plugin->getSettings();
        $primaryServicesCount = $settings->get('primary_services_count');
        $secondaryServicesCount =$settings->get( 'secondary_services_count' );

	    // $isServicesCountPrimaryServices = $settings->get('is_services_count_primary_services');

        if ($primaryServicesCount) {

	        $_services = $services;

            // if ($isServicesCountPrimaryServices) {
            $_services = array_filter($_services, function ($serviceID) {
                return !SLN_Plugin::getInstance()->createService($serviceID)->isSecondary();
            });
            // }

            $services = array_merge(array_slice($_services, 0, $primaryServicesCount), array_diff($services, $_services));
            
        }

        if( $secondaryServicesCount ){
            $_services = array_filter( $services, function( $servicesID ){
                return SLN_Plugin::getInstance()->createService( $serviceID )->isSecondary();
            });
            if( !empty( $_services ) ){
                $services = array_merge( array_slice( $services, 0, $secondaryServicesCount ), array_diff( $services, $_services ) );
            }
        }
        $builder->removeServices();
        

        foreach ($this->getServices(true, true) as $service) {
            $error = '';
            if (in_array($service->getId(), $services)) {
                $bb = $this->plugin->getBookingBuilder();
                $bookingServices = SLN_Wrapper_Booking_Services::build(array_fill_keys($services, 0), $this->getDateTime(), 0, $bb->getCountServices());
                $serviceErrors   = $this->ah->validateServiceFromOrder($service, $bookingServices);
                if(empty($serviceErrors)) {
                    $builder->addService($service);
                    $status = self::STATUS_CHECKED;
                }
                else {
                    unset($services[array_search($service->getId(), $services)]);
                    $status = self::STATUS_ERROR;
                    $error  = reset($serviceErrors);
                }
            } else {
                $status = self::STATUS_UNCHECKED;
            }
            $ret[$service->getId()] = array('status' => $status, 'error' => $error);
        }
        $builder->save();

        $servicesErrors = $this->ah->checkEachOfNewServicesForExistOrder($services, $newServices, true);
        foreach ($servicesErrors as $sId => $error) {
            if (empty($error)) {
                $ret[$sId] = array('status' => self::STATUS_UNCHECKED, 'error' => '');
            } else {
                $ret[$sId] = array('status' => self::STATUS_ERROR, 'error' => $error[0]);
            }
        }

	    $servicesExclusiveErrors = $this->ah->checkExclusiveServices( $services, array_merge( $merge, $newServices ) );
	    foreach ($servicesExclusiveErrors as $sId => $error) {
		    if (empty($error)) {
			    $ret[$sId] = array('status' => self::STATUS_UNCHECKED, 'error' => '');
		    } else {
			    $ret[$sId] = array('status' => self::STATUS_ERROR, 'error' => $error[0]);
		    }
	    }

        return $ret;
    }
}
