<?php
/**
 * @var $helper SLN_Admin_Settings
 */
?>
<div id="sln-salon_booking_system_required_pages" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Salon Booking System required pages', 'salon-booking-system');?></h2>
    <div class="collapse sln-box__panelcollapse">
    	<div class="row">
	<div class="col-xs-12 col-sm-6 col-md-4 form-group sln-select ">
	    <?php $helper->row_input_page('pay', __('Booking page', 'salon-booking-system'));?>
	    <p class="help-block"><?php _e('Select a page with the booking form.', 'salon-booking-system')?></p>
	</div>

	<div class="col-xs-12 col-sm-6 col-md-4 form-group sln-select ">
	    <?php $helper->row_input_page('thankyou', __('Thank you page', 'salon-booking-system'));?>
	    <p class="help-block"><?php _e(
	'Select a page where to redirect your users after booking completition.',
	'salon-booking-system'
)?></p>
	</div>
	<div class="col-xs-12 col-sm-6 col-md-4 form-group sln-select ">
	    <?php $helper->row_input_page('bookingmyaccount', __('Booking My Account', 'salon-booking-system'));?>
	    <p class="help-block"><?php _e(
	'Select a page where your users view their bookings.',
	'salon-booking-system'
)?></p>
	</div>
    </div>
</div>
</div>
