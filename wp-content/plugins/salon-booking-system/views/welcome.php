<?php
/**
 * Welcome Page View
 *
 * @since 1.0.0
 * @package salon-booking-system
 */
if (!defined('WPINC')) {
	die;
}
?>

      <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

      <!-- SET: Stylesheet -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
       <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<div class="wrapper">
		   <!---Salon Start Here-->
			 <div class="salon">
			  <div class="container">
				  <div class="salon_in">
				     <h1><strong><?php _e('Welcome!', 'salon-booking-system');?></strong> 					  <?php _e('Let’s begin our journey together.', 'salon-booking-system');?></h1>
				  </div>
				 </div>
			  </div>
		   <!---Salon End Here-->
		   <!---Dummy Start Here-->
			<div class="dummy">
			  <div class="container">
				 <div class="dummy_in">
				   <ul class="row d-flex flex-wrap">
					    <li class="col-xl-4">
						 <div class="service service_agenda">
							 <h4 class="service_title">
							 	<?php _e('Bookings agenda', 'salon-booking-system');?>
							 </h4>
							 <figure class="service_media">
							 	<img src="<?php echo plugin_dir_url(__DIR__); ?>img/welcome/bookings_agenda.png" alt="<?php _e('Bookings agenda', 'salon-booking-system');?>">
							</figure>
							 <h5 class="service_text"><?php _e('Where you can control and manage your appointments.', 'salon-booking-system');?></h5>
							 <div class="service_actions">
							 	<a href="/wp-admin/admin.php?page=salon" target="blank"><span><?php include WP_PLUGIN_DIR . '/salon-booking-plugin/img/welcome/arrow.php';?></span><?php _e('Let’s begin', 'salon-booking-system');?></a>
							 </div>
						 </div>
					   </li>
					   <li class="col-xl-4">
						 <div class="service service_form">
							 <h4 class="service_title">
							 	<?php _e('Bookings form', 'salon-booking-system');?>
							 </h4>
							 <figure class="service_media">
							 	<img src="<?php echo plugin_dir_url(__DIR__); ?>img/welcome/booking_form.png" alt="<?php _e('Bookings form', 'salon-booking-system');?>">
							</figure>
							 <h5 class="service_text"><?php _e('Where your clients can schedule an appointment with you.', 'salon-booking-system');?></h5>
							 <div class="service_actions">
							 	<a href="<?php echo get_permalink($settings->getPayPageId()) ?>" target="blank">
							 		<span><?php include WP_PLUGIN_DIR . '/salon-booking-plugin/img/welcome/arrow.php';?></span><?php _e('Let’s begin', 'salon-booking-system');?></a>
							 </div>
						 </div>
					   </li>
					   <li class="col-xl-4">
						 <div class="service service_settings">
							 <h4 class="service_title">
							 	<?php _e('Settings', 'salon-booking-system');?>
							 </h4>
							 <ul class="service_text service_text--list">
							 	<li><a href="/wp-admin/admin.php?page=salon-settings" target="blank"> <?php _e('Add salon information', 'salon-booking-system');?></a></li>
							 	<li><a href="/wp-admin/admin.php?page=salon-settings&tab=booking" target="blank"><?php _e('Set your scheuduling rules', 'salon-booking-system');?></a></li>
							 	<li><a href="/wp-admin/edit.php?post_type=sln_service" target="blank"><?php _e('Add your services', 'salon-booking-system');?></a></li>
							 	<li><a href="/wp-admin/edit.php?post_type=sln_attendant" target="blank"><?php _e('Add your Assistants', 'salon-booking-system');?></a></li>
							 </ul>
							 <div class="service_actions">
							 	<a href="/wp-admin/admin.php?page=salon-settings" target="blank"><span><?php include WP_PLUGIN_DIR . '/salon-booking-plugin/img/welcome/arrow.php';?></span><?php _e('Let’s begin', 'salon-booking-system');?></a>
							 </div>
						 </div>
					   </li>

					 </ul>
					 <div class="salon_book">
					   <a href="https://salonbookingsystem.com/">
					   	<img src="<?php echo plugin_dir_url(__DIR__); ?>img/welcome/salon_booking_welcome_logo.png" alt="Salon Booking System">
					   </a>
					 </div>
				  </div>
				</div>
			</div>
		   <!---Dummy End Here-->
		</div>