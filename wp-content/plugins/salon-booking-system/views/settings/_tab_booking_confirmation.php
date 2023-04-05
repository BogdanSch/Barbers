<?php
/**
 * @var $helper SLN_Admin_Settings
 */
?>
<div id="sln-booking_manual_confirmation" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Booking manual confirmation', 'salon-booking-system');?></h2>
    <div class="collapse sln-box__panelcollapse">
    <div class="row">
	<div class="col-xs-12 col-sm-6 col-md-4 form-group sln-checkbox">
	    <?php $helper->row_input_checkbox(
	'confirmation',
	__('Booking confirmation', 'salon-booking-system'),
	array('help' => '')
);?>
	</div>
	<div class="col-xs-12 col-sm-6 col-md-4 sln-box-maininfo  align-top">
	    <p class="sln-box-info"><?php _e('Select this option to manually confirm each booking.', 'salon-booking-system');?></p>
	</div>
    </div>
</div>
</div>
