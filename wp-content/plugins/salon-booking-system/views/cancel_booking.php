<?php
if ( ! defined( 'WPINC' ) ) {
die;
}

$plugin = SLN_Plugin::getInstance();
$settings = $plugin->getSettings();
$format = $plugin->format();
?>
<html>
    <head>
	<title>
	    <?php _e('Salon Booking System - Booking Cancellation', 'salon-booking-system'); ?>
	</title>
	<link rel='stylesheet' href='<?php echo is_rtl() ? SLN_PLUGIN_URL . '/css/cancel-booking-rtl.css' : SLN_PLUGIN_URL . '/css/cancel-booking.css' ?>' type='text/css' media='all' />
    </head>
    <body>
		<div class="sln-cancel-booking-block">
			<div class="sln-cancel-booking-block__logo">
				<?php $logo = $plugin->getSettings()->get('gen_logo');?>
				<img 
					src="<?php echo ($logo ? wp_get_attachment_image_url($logo, 'sln_gen_logo') : apply_filters('sln_default_email_logo', SLN_PLUGIN_URL . '/img/email/logo.png')); ?>"
					<?php echo (!$logo ? '' : 'width="100"') ?>
					alt="img"
					border="0">
			</div>
			<div>
				<div class="sln-cancel-booking-block__header">
					<?php echo ($settings->get('gen_name') ? $settings->get('gen_name') : get_bloginfo('name')); ?>
				</div>
				<div class="sln-cancel-booking-block__body">
					<div class="sln-cancel-booking-block__body__booking">
						<?php _e('Booking ID'); ?> <b><?php echo $booking->getId(); ?></b> | <?php echo $format->date($booking->getDate()), ' @ ', $format->time($booking->getTime()); ?>
					</div>
					<div class="sln-cancel-booking-block__body__action">
						<?php if ($booking->hasStatus(SLN_Enum_BookingStatus::CANCELED)): ?>
						<div class="sln-cancel-booking-block__body__action__booking-cancelled">
							<?php _e('Booking is cancelled', 'salon-booking-system'); ?>
						</div>
						<script>
							setTimeout(function () {
							window.location.href = '<?php echo $booking_url; ?>';
							}, 1000);
						</script>
						<?php elseif (!$cancellation_enabled): ?>
						<div class="sln-cancel-booking-block__body__action__cancellation-disabled">
							<?php _e('Cancellation is disabled', 'salon-booking-system'); ?>
						</div>
						<?php elseif ($out_of_time): ?>
						<div class="sln-cancel-booking-block__body__action__out_of_time">
							<?php _e('Out of time', 'salon-booking-system'); ?>
						</div>
						<?php else: ?>
						<div class="sln-cancel-booking-block__body__action__form-block">
							<form action="<?php echo $booking->getCancelUrl(); ?>" method="post" class="sln-cancel-booking-block__body__action__form-block__form">
								<input type="hidden" name="cancel_booking" value="1">
								<div>
									<button class="sln-cancel-booking-block__body__action__form-block__form__cancel-button">
									<?php _e('Cancel Booking', 'salon-booking-system'); ?>
									</button>
								</div>
							</form>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
    </body>
</html>