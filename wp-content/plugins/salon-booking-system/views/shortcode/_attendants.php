<?php
/**
 * @var SLN_Plugin                        $plugin
 * @var string                            $formAction
 * @var string                            $submitName
 * @var SLN_Shortcode_Salon_AttendantStep $step
 * @var SLN_Wrapper_Attendant[]           $attendants
 */

$ah = $plugin->getAvailabilityHelper();
$ah->setDate($plugin->getBookingBuilder()->getDateTime());
$bookingServices = SLN_Wrapper_Booking_Services::build($bb->getAttendantsIds(), $bb->getDateTime(), 0, $bb->getCountServices());

$hasAttendants = false;
$style         = $step->getShortcode()->getStyleShortcode();
$size          = SLN_Enum_ShortcodeStyle::getSize($style);


$services = $bb->getServices();
foreach ($services as $k => $service) {
    if (!$service->isAttendantsEnabled()) {
        unset($services[$k]);
    }
}

$isChooseAttendantForMeDisabled = $plugin->getSettings()->isChooseAttendantForMeDisabled();
$services_price = array();
foreach($services as $service){
    foreach($attendants as $attendant){
        $services_price[$attendant->getId()] += floatval($service->getVariablePrice($attendant->getId())) * intval($bb->getCountService($service->getId()));
    }
}
if($service->getVariablePriceEnabled()){
    $sort_func = function($att1, $att2)use($services_price){
        $price1 = $services_price[$att1->getId()];
        $price2 = $services_price[$att2->getId()];
        if($price1 == $price2){
            return 0;
        }
        return $price1 < $price2 ? 1 : -1;
    };
    usort($attendants, $sort_func);
}

$tmp = '';
$i = 0;
foreach ($attendants as $attendant) {
    if(get_post_status($attendant->getId()) == 'draft'){
        continue;
    }
    if ($attendant->hasServices($services)) {
        $errors = SLN_Shortcode_Salon_AttendantHelper::validateItem($bookingServices->getItems(), $ah, $attendant);
    if($plugin->getSettings()->get('hide_invalid_attendants_enabled') && !empty($errors)){
        continue;
    }

	if (!$i && $isChooseAttendantForMeDisabled) {
	    $tmp .= SLN_Shortcode_Salon_AttendantHelper::renderItem($size, $errors, $attendant, null, true, $services);
	} else {
	    $tmp .= SLN_Shortcode_Salon_AttendantHelper::renderItem($size, $errors, $attendant, null, false, $services);
	}

    $hasAttendants = true;
    if(is_null($errors)){
	    $i++;
    }
    }
}
if ($tmp && !$isChooseAttendantForMeDisabled) {
    $tmp = SLN_Shortcode_Salon_AttendantHelper::renderItem($size).$tmp;
}

$isSymbolLeft = $plugin->getSettings()->get('pay_currency_pos') == 'left';
$symbolLeft = $isSymbolLeft ? $plugin->getSettings()->getCurrencySymbol() : '';
$symbolRight = $isSymbolLeft ? '' : $plugin->getSettings()->getCurrencySymbol();
$decimalSeparator = $plugin->getSettings()->getDecimalSeparator();
$thousandSeparator = $plugin->getSettings()->getThousandSeparator();
$showPrices = ($plugin->getSettings()->get('hide_prices') != '1') ? true : false;

if ($showPrices) {
    $_showPrices = false;
    foreach($services as $service) {
        if ($service->getVariablePriceEnabled()) {
            $_showPrices = true;
        }
    }
    $showPrices = $_showPrices;
}

?>
<div class="sln-attendant-list">
    <?php if($i > 1): ?>
    <div class="row">
        <?php $icons = $step->renderSortIcon();
        echo $icons[0], $icons[1]; ?>
    </div>
    <?php endif ?>
    <?php if ($tmp) : ?>
        <div class="row"><?php echo $tmp ?></div>
    <?php else: ?>
        <div class="alert alert-warning">
            <p><?php echo apply_filters('sln.template.shortcode.attendant.emptyAttendantsList', __(
                    'No assistants available for the selected time/slot - please choose another one',
                    'salon-booking-system'
                )) ?></p>
        </div>
    <?php endif ?>
</div>
<?php if ($showPrices) { ?>
    <div class="row sln-total">
        <?php if ($size == '900'): ?>
            <h3 class="col-xs-6 col-sm-6 col-md-6 sln-total-label">
                <?php _e('Subtotal', 'salon-booking-system') ?>
            </h3>
            <h3 class="col-xs-6 col-sm-6 col-md-6 sln-total-price" id="services-total"
                data-symbol-left="<?php echo $symbolLeft ?>"
                data-symbol-right="<?php echo $symbolRight ?>"
                data-symbol-decimal="<?php echo $decimalSeparator ?>"
                data-symbol-thousand="<?php echo $thousandSeparator ?>">
                <?php echo $plugin->format()->money(0, false) ?>
            </h3>
        <?php elseif ($size == '600'): ?>
            <h3 class="col-xs-6 sln-total-label">
                <?php _e('Subtotal', 'salon-booking-system') ?>
            </h3>
            <h3 class="col-xs-6 sln-total-price" id="services-total"
                data-symbol-left="<?php echo $symbolLeft ?>"
                data-symbol-right="<?php echo $symbolRight ?>"
                data-symbol-decimal="<?php echo $decimalSeparator ?>"
                data-symbol-thousand="<?php echo $thousandSeparator ?>">
                <?php echo $plugin->format()->money(0, false) ?>
            </h3>
        <?php elseif ($size == '400'): ?>
            <h3 class="col-xs-6 sln-total-label">
                <?php _e('Subtotal', 'salon-booking-system') ?>
            </h3>
            <h3 class="col-xs-6 sln-total-price" id="services-total"
                data-symbol-left="<?php echo $symbolLeft ?>"
                data-symbol-right="<?php echo $symbolRight ?>"
                data-symbol-decimal="<?php echo $decimalSeparator ?>"
                data-symbol-thousand="<?php echo $thousandSeparator ?>">
                <?php echo $plugin->format()->money(0, false) ?>
            </h3>
        <?php else: throw new Exception('size not supported'); ?>
        <?php endif ?>
    </div>
<?php } ?>

