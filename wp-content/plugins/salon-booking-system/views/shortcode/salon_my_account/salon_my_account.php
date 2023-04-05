<!-- algolplus -->
<?php $cce = !$plugin->getSettings()->isCustomColorsEnabled();?>
<div id="sln-salon" class="sln-bootstrap <?php if (!$cce) {
	echo ' sln-customcolors';
}?>">
	<div id="sln-salon-my-account">
		<div id="sln-salon-my-account-content">
		</div>
	</div>
</div>
<?php if(defined('SLN_SPECIAL_EDITION') && SLN_SPECIAL_EDITION): ?>
<div id="sln-plugin-credits"><?php _e('Proudly powered by', 'salon-booking-system') ?> <a target="_blanck" href="https://www.salonbookingsystem.com/plugin-pricing/#utm_source=plugin-credits&utm_medium=booking-my-account&utm_campaign=booking-my-account&utm_id=plugin-credits"><?php _e('Salon Booking System', 'salon-booking-system'); ?></a></div>
<?php endif; ?>
