<div class="col-xs-12 col-sm-4 col-md-2 sln-booking-discounts">
	<div class="form-group sln-input--simple">
		<?php foreach ($discounts as $discount): ?>
		    <label for=""><?php _e('Discount applied', 'salon-booking-system'); ?></label>

		    <div>
			    <label><?php echo $discount->getAmountString(); ?></label>
		    </div>
		<?php endforeach; ?>
	</div>
</div>
