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

$isSymbolLeft = $plugin->getSettings()->get('pay_currency_pos') == 'left';
$symbolLeft = $isSymbolLeft ? $plugin->getSettings()->getCurrencySymbol() : '';
$symbolRight = $isSymbolLeft ? '' : $plugin->getSettings()->getCurrencySymbol();
$decimalSeparator = $plugin->getSettings()->getDecimalSeparator();
$thousandSeparator = $plugin->getSettings()->getThousandSeparator();
$showPrices = ($plugin->getSettings()->get('hide_prices') != '1') ? true : false;

$_showPrices = false;

$isChooseAttendantForMeDisabled = $plugin->getSettings()->isChooseAttendantForMeDisabled();

foreach ($bookingServices->getItems() as $bookingService) :
    $service = $bookingService->getService();
    if($service->getVariablePriceEnabled()){
        $sort_func = function($att1, $att2)use($service){
            $price1 = $service->getVariablePrice($att1->getId());
            $price2 = $service->getVariablePrice($att2->getId());
            if($price1 == $price2){
                return 0;
            }
            return $price1 < $price2 ? 1 : -1;
        };
        usort($attendants, $sort_func);
    }

    if ($service->getVariablePriceEnabled()) {
        $_showPrices = true;
    }

    if ($service->isAttendantsEnabled()) {
        $tmp = '';
	$i = 0;
        foreach ($attendants as $attendant) {
            if(get_post_status($attendant->getId()) == 'draft'){
                continue;
            }
            if ($attendant->hasServices(array($service))) {
                $errors = SLN_Shortcode_Salon_AttendantHelper::validateItem(array($bookingService), $ah, $attendant);

                if($plugin->getSettings()->get('hide_invalid_attendants_enabled') && !empty($errors)){
                    continue;
                }

                if (!$i && $isChooseAttendantForMeDisabled) {
                    $tmp .= SLN_Shortcode_Salon_AttendantHelper::renderItem($size, $errors, $attendant, $service, true);
                } else {
                    $tmp .= SLN_Shortcode_Salon_AttendantHelper::renderItem($size, $errors, $attendant, $service);
                }

                $i++;
            }
        }
        if ($tmp && !$isChooseAttendantForMeDisabled) {
            $tmp = SLN_Shortcode_Salon_AttendantHelper::renderItem($size, null, null, $service).$tmp;
        }
    }

    if ($showPrices) {
        $showPrices = $_showPrices;
    }


    ?>
    <div class="sln-attendant-list sln-attendant-list--multiple">
        <div class="row">
            <div class="col-xs-12">
                <h3 class="sln-steps-name sln-service-name"><?php echo $service->getName() ?></h3>
            </div>
        </div>
        <div class="row">
            <?php $icon = $step->renderSortIcon();
            echo $icon[0], $icon[1]; ?>
        </div>
        <?php if ($service->isAttendantsEnabled()) : ?>
            <?php if ($tmp) : ?>
                <?php echo $tmp ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p><?php echo apply_filters('sln.template.shortcode.attendant.emptyAttendantsList', __(
                            'No assistants available for the selected time/slot - please choose another one',
                            'salon-booking-system'
                        )) ?></p>
                </div>
            <?php endif ?>
        <?php else: ?>
            <div class="row sln-attendant">
                <?php SLN_Form::fieldText('sln[attendants]['.$service->getId().']', 0, array('type' => 'hidden')) ?>
                <p><?php echo __(
                        'The choice of assistant is not provided for this service',
                        'salon-booking-system'
                    ) ?></p>
            </div>
        <?php endif ?>
    </div>
<?php endforeach ?>

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



