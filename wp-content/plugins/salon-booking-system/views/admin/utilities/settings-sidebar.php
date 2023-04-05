<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="sln-admin-sidebar <?php if (!defined("SLN_VERSION_PAY") || !SLN_VERSION_PAY) {echo " sln-admin-sidebar--free";}?>">
	<div class="sln-update-settings__wrapper">
		<div class="sln-btn sln-btn--main sln-btn--big sln-btn--icon sln-icon--save sln-update-settings">
			<input type="submit" name="submit" id="submit" class="" value="Update Settings">
		</div>
	</div>

	<?php if (!defined("SLN_VERSION_PAY") || !SLN_VERSION_PAY) {
	?>
	<div class="sln-admin-banner">
		<div class="col-md4"></div>
		<div class="col-md4">

			<div class="sln-promo-message">


			</div>


		</div>
		<div class="col-md4"></div>
	</div>
	<?php }?>
    <?php if ( ! in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ): ?>
	<div class="sln-help-button__block">
		<button class="sln-help-button sln-btn sln-btn--nobkg sln-btn--big sln-btn--icon sln-icon--helpchat sln-btn--icon--al visible-md-inline-block visible-lg-inline-block"><?php _e('Do you need help ?', 'salon-booking-system')?></button>
    	<button class="sln-help-button sln-btn sln-btn--mainmedium sln-btn--small--round sln-btn--icon  sln-icon--helpchat sln-btn--icon--al hidden-md hidden-lg"><?php _e('Do you need help ?', 'salon-booking-system')?> </button>
	</div>
    <?php endif; ?>
</div>
<div class="clearfix"></div>