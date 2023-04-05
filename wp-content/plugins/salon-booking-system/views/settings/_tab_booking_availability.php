<?php
/**
 * @var $plugin SLN_Plugin
 */
$mode = $plugin->getSettings()->get('availability_mode');
?>
<div id="sln-availability_mode" class="sln-box sln-box--main sln-box--haspanel">
<h2 class="sln-box-title sln-box__paneltitle"><?php _e('Availability mode', 'salon-booking-system');?> <span><?php _e('Select your favourite booking system mode.', 'salon-booking-system')?></span></h2>
<div class="collapse sln-box__panelcollapse">
<div class="row">
    <div class="col-xs-12 col-sm-8">
        <div class="sln-radiobox">
            <?php $field = "salon_settings[availability_mode]";?>
            <?php echo SLN_Form::fieldRadioboxGroup(
	$field,
	SLN_Enum_AvailabilityModeProvider::toArray(),
	$mode,
	array(),
	true
)

?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4 form-group sln-box-maininfo">
        <p class="sln-box-info"><?php _e('Choose which kind of booking algorithm want to use for your salon according to your specific booking needs.', 'salon-booking-system');?></p>
    </div>
</div>
</div>
</div>
