<div class="wrap sln-bootstrap">
	<h1><?php _e( 'Reports', 'salon-booking-system' ) ?></h1>
	<?php include '_reports_bookings.php' ?>
</div>
<?php if ( ! in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ): ?>
<script>
window.Userback = window.Userback || {};
Userback.access_token = '33731|64310|7TOMg95VWdhaFTyY2oCZrnrV3';
(function(d) {
var s = d.createElement('script');s.async = true;
s.src = 'https://static.userback.io/widget/v1.js';
(d.head || d.body).appendChild(s);
})(document);
</script>
<?php endif; ?>