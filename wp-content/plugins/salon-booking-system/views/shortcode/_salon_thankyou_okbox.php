<?php if ($pendingPayment):
	$booking = $plugin->getBookingBuilder()->getLastBooking(); ?>
	<div class="row">
		<div class="col-xs-12">
		    <div class="sln-thankyou--okbox sln-bkg--ok sln-pending-payment-box">
			    <div class="row">
					<div class="col-xs-12">
					    <h2 class="sln-pending-payment-box--header">
						<img class="sln-pending-payment-box--icon" src="<?php echo SLN_PLUGIN_URL.'/img/pay_icon.png' ?>" alt="">
						<strong>
						    <?php echo __('CHOOSE A PAYMENT OPTION', 'salon-booking-system') ?>
						</strong>
					    </h2>
					</div>
					<div class="col-xs-12 sln-pending-payment-box--booking-number">
					    <?php _e('Pending booking number', 'salon-booking-system') ?>:
					    <strong>
						<?php echo $booking->getId() ?>
					    </strong>
					</div>
					<?php if($booking->getDeposit() > 0){ ?>
						<div class="col-xs-12 sln-pending-payment-box--amount">
						    <?php _e('Total amount of the reservation', 'salon-booking-system') ?>:
						    <strong>
							<?php echo $plugin->format()->moneyFormatted($booking->getAmount()) ?>
						    </strong>
						</div>
						<div class="col-xs-12 sln-pending-payment-box--amount">
						    <?php _e('Amount to be paid in advance', 'salon-booking-system') ?>:
						    <strong>
							<?php echo $plugin->format()->moneyFormatted($booking->getDeposit()) ?>
						    </strong>
						</div>
					<?php }else{ ?>
						<div class="col-xs-12 sln-pending-payment-box--amount">
						    <?php _e('Amount to be paid', 'salon-booking-system') ?>:
						    <strong>
							<?php echo $plugin->format()->moneyFormatted($booking->getAmount()) ?>
						    </strong>
						</div>
					<?php } ?>
				</div>
		    </div>
		</div>
	</div>
<?php else: ?>
	<div class="row">
		<div class="col-xs-12">
		    <div class="sln-thankyou--okbox <?php if($confirmation): ?> sln-bkg--attention<?php else : ?> sln-bkg--ok<?php endif ?>">
				<div class="row">
					<div class="col-xs-12">
					    <h2 class="sln-icon-wrapper"><?php echo $confirmation ? __('Your booking is pending', 'salon-booking-system') : __('Your booking is completed', 'salon-booking-system') ?>
						<?php if($confirmation): ?>
						    <i class="sln-icon sln-icon--time"></i>
						<?php else : ?>
						    <i class="sln-icon sln-icon--checked--square"></i>
						<?php endif ?>
					    </h2>
					</div>
					<div class="col-xs-12"><hr></div>
					<div class="col-xs-12">
					    <h3 class="salon-step-title"><?php _e('Booking number', 'salon-booking-system') ?></h3>
					    <h4><?php echo $plugin->getBookingBuilder()->getLastBooking()->getId() ?></h4>
					</div>
				</div>
		    </div>
		</div>
	</div>
<?php endif; ?>
