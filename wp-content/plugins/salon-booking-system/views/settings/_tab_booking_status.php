<?php
/**
 * @var $plugin SLN_Plugin
 */
$disabled = $plugin->getSettings()->get('disabled');
$disabledMessage = $plugin->getSettings()->get('disabled_message');
?>
<div id="sln-pause_booking_service" class="sln-box sln-box--main sln-box--haspanel sln-box--haspanel--open">
<h2 class="sln-box-title sln-box__paneltitle sln-box__paneltitle--open"><?php _e('Pause booking service <span class="block">If <strong>OFF</strong> the online booking form will be disabled and your users will see a message.</span>', 'salon-booking-system');?></h2>
<div class="collapse in sln-box__panelcollapse">
<div class="row">
    <div class="col-xs-12 col-sm-6 sln-switch sln-switch--inverted sln-moremargin--bottom">
        <h6 class="sln-fake-label"><?php _e('Online Booking Status', 'salon-booking-system');?></h6>
        <?php SLN_Form::fieldCheckboxSwitch(
	"salon_settings[disabled]",
	$disabled,
	$labelOn = "Online booking ON",
	$labelOff = "Online booking OFF"
)?>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6 form-group sln-input--simple">
        <label for="<?php echo SLN_form::makeID("salon_settings[disabled_message]") ?>"><?php _e(
	'Message on disabled booking',
	'salon-booking-system'
)?></label></strong>
        <?php
$admin_email = $plugin->getSettings()->getSalonEmail();
SLN_Form::fieldTextarea(
	"salon_settings[disabled_message]",
	$disabledMessage,
	array(
		'attrs' => array(
			'placeholder' => __('Booking is not available at the moment, please contact us at ', 'salon-booking-system') . $admin_email,
			'rows' => 5,
			'class' => 'form-control',
			'style' => 'width: 100%;',
		),
	)
)?>
    </div>
</div>
</div>
</div>
