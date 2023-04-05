<div class="wrap sln-bootstrap" id="sln-salon--admin">
	<h1><?php _e('Customers', 'salon-booking-system') ?>
		<?php /** @var string $new_link */ ?>
	<a href="<?php echo $new_link; ?>" class="page-title-action"><?php _e('Add Customer', 'salon-booking-system'); ?></a>
	</h1>

<form method="get">
	<input type="hidden" name="page" class="post_type_page" value="salon-customers">
	<?php
	/** @var SLN_Admin_Customers_List $table */
	$table->display();
	?>
</form>
<br class="clear" />

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