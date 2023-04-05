<?php
/**
 * @var SLN_Plugin $plugin
 * @var string $formAction
 * @var string $submitName
 * @var SLN_Shortcode_Salon_Step $step
 */
$bb = $plugin->getBookingBuilder();
$currencySymbol = $plugin->getSettings()->getCurrencySymbol();
$datetime = $plugin->getSettings()->isDisplaySlotsCustomerTimezone() && $bb->get('customer_timezone') ? $bb->getDateTimeCustomerTimezone() : $bb->getDateTime();
$confirmation = $plugin->getSettings()->get('confirmation');
$showPrices = ($plugin->getSettings()->get('hide_prices') != '1') ? true : false;
$style = $step->getShortcode()->getStyleShortcode();
$size = SLN_Enum_ShortcodeStyle::getSize($style);
$isTipRequestEnabled = $plugin->getSettings()->isTipRequestEnabled();
$tipsValue = $bb->getTips();
if ($errors && in_array(SLN_Shortcode_Salon_SummaryStep::SLOT_UNAVAILABLE, $errors)){
    echo $plugin->loadView('shortcode/_unavailable', array('step' => $step));
}else if ($errors && in_array(SLN_Shortcode_Salon_SummaryStep::SERVICES_DATA_EMPTY, $errors)){
    echo $plugin->loadView('shortcode/_services_data_empty', array('step' => $step));
}else{
?>
<form method="post" action="<?php echo $formAction ?>" role="form" id="salon-step-summary">
    <?php echo apply_filters('sln.booking.salon.summary-step.add-params-html', '') ?>
    <?php
    $args = array(
        'label'        => __('Booking summary', 'salon-booking-system'),
        'tag'          => 'h2',
        'textClasses'  => 'salon-step-title',
        'inputClasses' => '',
        'tagClasses'   => 'salon-step-title',
    );
    echo $plugin->loadView('shortcode/_editable_snippet', $args);
    ?>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <p class="sln-text--dark">
                <?php
                $name = array();
                if (!SLN_Enum_CheckoutFields::getField('firstname')->isHidden()) {
                    $firstname = esc_attr($bb->get('firstname'));
                    if (!empty($firstname)) {
                        $name[] = $firstname;
                    }
                }
                if (!SLN_Enum_CheckoutFields::getField('lastname')->isHidden()) {
                    $lastname = esc_attr($bb->get('lastname'));
                    if (!empty($lastname)) {
                        $name[] = $lastname;
                    }
                }
                $name = implode(' ', $name);

                if (!empty($name)) {
                    _e('Dear', 'salon-booking-system');
                ?>
                    <strong><?php echo $name.','; ?></strong>
                    <br/>
                <?php } ?>
                <?php _e('please review and confirm the details of your booking:', 'salon-booking-system') ?>
            </p>
        </div>
    </div>
    <?php include '_salon_summary_'.$size.'.php'; ?>
    <?php include '_errors.php'; ?>
</form>
<?php
}