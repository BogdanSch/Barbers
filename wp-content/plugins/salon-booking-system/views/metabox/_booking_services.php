<?php

$servicesData = array(0 => array(
    'title' => ', ' . __('Select a service', 'salon-booking-system'),
    'name' => ', ' . __('Select a service', 'salon-booking-system'),
    'price' => 0,
    'duration' => 0,
    'break_duration' => 0,
    'exec_order' => 0,
    'attendants' => array(0),
));
$formatter = $plugin->format();
$isAttendants = $plugin->getSettings()->isAttendantsEnabled();
$isMultipleAttendants = $plugin->getSettings()->isMultipleAttendantsEnabled();
$isAttendants = $isAttendants || $booking->getAttendant();
$isMultipleAttendants = $isAttendants && ($isMultipleAttendants || (count($booking->getAttendants(true)) > 1));
/** @var SLN_Repository_ServiceRepository $sRepo */
$sRepo = $plugin->getRepository(SLN_Plugin::POST_TYPE_SERVICE);
$allServices = $sRepo->getAll();
$allServices = $sRepo->sortByExecAndTitleDESC($allServices);
$allServices = apply_filters('sln.shortcode.salon.ServicesStep.getServices', $allServices);

/** @var SLN_Repository_AttendantRepository $sRepo */
$sRepo = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
$allAttendants = $sRepo->getAll();
$allAttendants = apply_filters('sln.shortcode.salon.AttendantStep.getAttendants', $allAttendants);
$attendantsData = array();
foreach ($allAttendants as $attendant) {
	$attendantsData[$attendant->getId()] = $attendant->getName();
}
$allServicesSelectionArray = array(0 => ', ' . __('Select a service', 'salon-booking-system'));
foreach($allServices as $service){
    $serviceCategoryName = !empty($service->getServiceCategory()) ? $service->getServiceCategory()->getName(). ', ' : ', ';
    $allServicesSelectionArray[$service->getId()] = $serviceCategoryName . $service->getName() . ', ' .
    $formatter->money($service->getPrice()) . ' - ' .
    $service->getDuration()->format('H:i');
    $servicesData[$service->getId()] = array(
			'title' => $serviceCategoryName . $service->getName() . ', ' . $formatter->money($service->getPrice()) . ' - ' . $service->getDuration()->format('H:i'),
			'name' => $service->getName(),
			'price' => $service->getPrice(),
			'duration' => SLN_Func::getMinutesFromDuration($service->getDuration()),
			'break_duration' => SLN_Func::getMinutesFromDuration($service->getBreakDuration()),
			'exec_order' => $service->getExecOrder(),
			'attendants' => $service->getAttendantsIds(),
                        'isMultipleAttendants' => $service->isMultipleAttendantsForServiceEnabled(),
                        'countMultipleAttendants' => $service->getCountMultipleAttendants(),
                        'isAttendantsEnabled' => $service->isAttendantsEnabled(),
		);
}

?>
    <div id="sln_booking_services" class="form-group sln_meta_field row">
        <div class="col-xs-12 col-sm-12 col-md-12">
                <?php if ($isMultipleAttendants || $isAttendants ): ?>
                <h4 class="sln-box-title--nu--sec"><?php _e('Services & Attendants', 'salon-booking-system');?></h4>
                <?php else: ?>
                <h4 class="sln-box-title--nu--sec"><?php _e('Services', 'salon-booking-system');?></h4>
                <?php endif;?>
                <span id="sln-alert-noservices" class="sln-alert sln-alert--warning sln-alert--inline"><?php echo __('No services addded yet', 'salon-booking-system') ?></span>
        </div>

        <?php ob_start();?>
        <div class="col-xs-12 col-sm-12 col-md-12 sln-booking-service-line">
                <div class="sln-row">
                    <div class="sln-booking-service--col-1 sln-booking-service--move-line">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
            <?php if ('highend' == $plugin->getSettings()->getAvailabilityMode()): ?>
                <div class="sln-booking-service--col-1">
                    <label class="time"></label>
                </div>
                <div class="sln-booking-service--col-1">
                    <label class="time"></label>
                </div>
                <div class="sln-booking-service--col-5 sln-select">
            <?php else: ?>
                <div class="sln-booking-service--col-6  sln-select">
            <?php endif;?>
                <?php SLN_Form::fieldSelect(
	'_sln_booking[services][__service_id__]',
	$allServicesSelectionArray,
	'__service_id__',
	array(
		'attrs' => array(
			'data-price' => '__service_price__',
			'data-duration' => '__service_duration__',
			'data-break' => '__service_break_duration__',
            'data-selection' => 'service-selected',
		),
		'no_id' => true,
	),
	true
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[service][__service_id__]',
	'__service_id__',
	array('type' => 'hidden')
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[price][__service_id__]',
	'__service_price__',
	array('type' => 'hidden')
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[duration][__service_id__]',
	'__service_duration__',
	array('type' => 'hidden')
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[break_duration][__service_id__]',
	'__service_break_duration__',
	array('type' => 'hidden')
)
?>
            </div>
            <?php if ($isMultipleAttendants || $isAttendants): ?>
            <div class="sln-booking-service--col-3 sln-select">
            <p class="sln-alert sln-alert--multiple sln-alert--warning sln-alert--inline hide" data-alert="<?php _e('more assistant') ?>"></p>
            <p class="sln-no-attendant-required hide"><?php _e('No assistant required', 'salon-booking-system') ?></p>
                <?php SLN_Form::fieldSelect(
	'_sln_booking[attendants][__service_id__]',
	array('__attendant_id__' => '__attendant_name__'),
	'__attendant_id__',
	array('attrs' => array('data-service' => '__service_id__', 'data-attendant' => '', 'data-selection' => 'attendant-selected'),
		'no_id' => true),
	true
)?>
            </div>
            <?php endif?>
            <div class="sln-booking-service--col-1">
                <span class="sln-alert sln-alert--fadeout sln-alert--ok sln-alert--onremove hide"><?php echo __('Service addded', 'salon-booking-system') ?></span>
                    <button class="sln-btn sln-btn--big sln-btn--icon sln-btn--icon--left--alt sln-icon--times sln-btn--textonly sln-btn--textonly--emph" data-collection="remove"><?php echo __('Remove', 'salon-booking-system') ?></button>
            </div>
        </div>
    </div>
        <div class="clearfix"></div>
        <?php
$lineItem = ob_get_clean();
$lineItem = preg_replace("/\r\n|\n/", ' ', $lineItem);
?>
        <div class="col-xs-12">
            <div class="sln-row">
                <div class="sln-booking-service--col-1 sln-booking-service--move-line"></div>
            <?php if ('highend' == $plugin->getSettings()->getAvailabilityMode()): ?>
                <div class="sln-booking-service--col-1"><h5 class="sln-box-title--nu--ter"><?php _e('Start at', 'salon-booking-system')?></h5></div>
                <div class="sln-booking-service--col-1"><h5 class="sln-box-title--nu--ter"><?php _e('End at', 'salon-booking-system')?></h5></div>
            <?php endif;?>
            <?php if ('highend' == $plugin->getSettings()->getAvailabilityMode()): ?>
                <div class="sln-booking-service--col-5">
            <?php else: ?>
                <div class="sln-booking-service--col-6">
            <?php endif; ?>
                    <h5 class="sln-box-title--nu--ter"><?php _e('Service', 'salon-booking-system')?></h5>
                 </div>
            <?php if ($isMultipleAttendants || $isAttendants): ?>
            <div class="sln-booking-service--col-3"><h5 class="sln-box-title--nu--ter"><?php _e('Attendant', 'salon-booking-system')?></h5></div>
            <?php endif; ?>
            <div class="sln-booking-service--col-1"><h5 class="sln-box-title--nu--ter"></h5></div>
            </div>
        </div>

<?php foreach ($booking->getBookingServices()->getItems() as $bookingService): ?>
                <?php
$serviceName = $bookingService->getService()->getName();
$serviceId = $bookingService->getService()->getId();

$servicesData[$serviceId] = array_merge(isset($servicesData[$serviceId]) ? $servicesData[$serviceId] : array(),
    array(
        'old_price' => $bookingService->getPrice(),
        'old_duration' => SLN_Func::getMinutesFromDuration($bookingService->getDuration()),
        'old_break_duration' => SLN_Func::getMinutesFromDuration($bookingService->getBreakDuration()),
    )
);
?>

        <div class="col-xs-12 col-sm-12 col-md-12 sln-booking-service-line">
            <div class="sln-row">
                <div class="sln-booking-service--col-1 sln-booking-service--move-line">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>

            <?php if ('highend' == $plugin->getSettings()->getAvailabilityMode()): ?>
                <div class="sln-booking-service--col-1">
                    <label class="time"><?php echo $formatter->time($bookingService->getStartsAt()) ?></label>
                </div>
                <div class="sln-booking-service--col-1">
                    <label class="time"><?php echo $formatter->time($bookingService->getEndsAt()) ?></label>
                </div>
            <?php endif;?>
            <?php if ('highend' == $plugin->getSettings()->getAvailabilityMode()): ?>
                <div class="sln-booking-service--col-5 sln-select">
            <?php else: ?>
                <div class="sln-booking-service--col-6 sln-select">
            <?php endif;?>
                <?php SLN_Form::fieldSelect(
	'_sln_booking[services][]',
	$allServicesSelectionArray,
	$bookingService->getService()->getId(),
	array(
		'attrs' => array(
			'data-price' => $servicesData[$serviceId]['old_price'],
			'data-duration' => $servicesData[$serviceId]['old_duration'],
            'data-selection' => 'service-selected',
		),
		'no_id' => true,
	),
	true
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[service][' . $serviceId . ']',
	$serviceId,
	array('type' => 'hidden')
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[price][' . $serviceId . ']',
	$servicesData[$serviceId]['old_price'],
	array('type' => 'hidden')
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[duration][' . $serviceId . ']',
	$servicesData[$serviceId]['old_duration'],
	array('type' => 'hidden')
)
?>
                <?php SLN_Form::fieldText(
	'_sln_booking[break_duration][' . $serviceId . ']',
	$servicesData[$serviceId]['old_break_duration'],
	array('type' => 'hidden')
)
?>
            </div>
            <?php if ($isMultipleAttendants || $isAttendants): ?>
            <div class="sln-booking-service--col-3 sln-select">
                <p class="sln-alert sln-alert--multiple sln-alert--warning sln-alert--inline hide" data-alert="<?php _e('more assistant') ?>"></p>
                <p class="sln-no-attendant-required hide"><?php _e('No assistant required', 'salon-booking-system') ?></p>
                <?php
                    $attendant = $bookingService->getAttendant();
                    $multipleAttr = is_array($attendant) ? array('multiple' => '') : array();
                    if(!is_array($attendant) && $attendant){
                        $attendantItem = array($attendant->getId() => $attendant->getName());
                    }elseif(is_array($attendant)){
                        $attendantItem = array();
                        foreach($attendant as $att){
                            $attendantItem[$att->getId()] = $att->getName();
                        }
                    }
                    SLN_Form::fieldSelect(
                        '_sln_booking[attendants][' . $serviceId . ']' . (is_array($attendant) ? '[]' : ''),
                        ($attendant ? $attendantItem : array('')),
                        ($attendant ? array_keys($attendantItem) : ''),
                        array('attrs' => array('data-service' => $serviceId, 'data-attendant' => '', 'data-selection' => 'attendant-selected') + $multipleAttr,
                            'no_id' => true),
                        true
                    );
                ?>
            </div>
            <?php endif?>
            <div class="sln-booking-service--col-1">
                    <span class="sln-alert sln-alert--fadeout sln-alert--ok sln-alert--onremove"><?php echo __('Service addded', 'salon-booking-system') ?></span>
                    <button class="sln-btn sln-btn--big sln-btn--icon sln-btn--icon--left--alt sln-icon--times sln-btn--textonly sln-btn--textonly--emph" data-collection="remove"><?php echo __('Remove', 'salon-booking-system') ?></button>
            </div>

            </div>
        </div>
        <div class="clearfix"></div>
<?php endforeach?>

        <div class="col-xs-12 col-sm-12 col-md-12 sln-booking-service-action">
        </div>
        <div class="row">
             <?php if ($isMultipleAttendants): ?>
                <div class="col-xs-12 col-sm-5 col-sm-offset-2 col-md-offset-2 sln-booking-service-action__btns">
            <?php else: ?>
                <div class="col-xs-12 col-sm-5 col-lg-6 sln-booking-service-action__btns">
            <?php endif;?>
                <button id="sln-addservice" data-collection="addnewserviceline" class="sln-btn sln-btn--big sln-btn--icon sln-btn--icon--left--alt sln-icon--plus sln-btn--nu--highemph">
                    <?php _e('Add a service', 'salon-booking-system')?>
                </button>
            </div>
        </div>
        <script>
            var servicesData = '<?php echo addslashes(json_encode($servicesData)); ?>';
            var attendantsData = '<?php echo addslashes(json_encode(array(0 => __('Select an assistant', 'salon-booking-system')) + $attendantsData)); ?>';
            var lineItem = '<?php echo addslashes($lineItem); ?>';
        </script>

            </div>
        </div>

