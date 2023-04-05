<?php

if (!isset($forAdmin)) {
	$forAdmin = false;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <title>Salon Booking email</title>
      <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&amp;subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese" rel="stylesheet">
      <link href="https://fonts.googleapis.com/css?family=Barlow+Condensed:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&amp;subset=latin-ext,vietnamese" rel="stylesheet">
      <style type="text/css">

      .wrap000 {
        padding:50px 0;
      }
         /* Outlook link fix */
         #outlook a {
         padding: 0;
         }
         /* Hotmail background &amp; line height fixes */
         .ExternalClass {
         width: 100% !important;
         }
         .ExternalClass,
         .ExternalClass p,
         .ExternalClass span,
         .ExternalClass font,
         .ExternalClass td,
         .ExternalClass div {
         line-height: 100%;
         }
         /* Image borders &amp; formatting */
         img {
         outline: none;
         text-decoration: none;
         -ms-interpolation-mode: bicubic;
         margin: 0 0 0 0 !important;
         }
         a img {
         border: none !important;
         margin: 0 0 0 0 !important;
         }
         /* Re-style iPhone automatic links (eg. phone numbers) */
         .applelinks a {
         color: #222222;
         text-decoration: none;
         }
         /* Hotmail symbol fix for mobile devices */
         .ExternalClass img[class^=Emoji] {
         width: 10px !important;
         height: 10px !important;
         display: inline !important;
         }
         @font-face {
         font-family: 'Avenir-Medium';
         src: url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Medium/Avenir-Medium.eot?#iefix' ?>') format('embedded-opentype'),
         url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Medium/Avenir-Medium.woff' ?>') format('woff'), url('Avenir-Medium.ttf')  format('truetype'),
         url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Medium/Avenir-Medium.svg#Avenir-Medium' ?>') format('svg');
         font-weight: normal;
         font-style: normal;
         }
         @font-face {
         font-family: 'Avenir-Black';
         src: url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Black/Avenir-Black.eot?#iefix' ?>') format('embedded-opentype'),
         url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Black/Avenir-Black.woff' ?>') format('woff'), url('Avenir-Black.ttf')  format('truetype'),
         url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Black/Avenir-Black.svg#Avenir-Black' ?>') format('svg');
         font-weight: normal;
         font-style: normal;
         }
         @font-face {
         font-family: 'Avenir-Roman';
         src: url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Roman/Avenir-Roman.eot?#iefix' ?>') format('embedded-opentype'),
         url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Roman/Avenir-Roman.woff' ?>') format('woff'), url('Avenir-Roman.ttf')  format('truetype'),
         url('<?php echo plugin_dir_url(dirname(__DIR__)) . 'fonts/email/Avenir-Roman/Avenir-Roman.svg#Avenir-Roman' ?>') format('svg');
         font-weight: normal;
         font-style: normal;
         }
         /* Media Query for mobile */
          @media screen and (max-width:660px) {
        .wrap1001{width: 100%!important;margin: 0 auto !important;text-align: center!important;}
        .wrap1002{width: 96%!important;margin: 0 auto !important;text-align: center!important;}
              .pad1{padding:0 7px!important;}

          }
         @media screen and (max-width:575px) {

.pad1{display:block!important; margin: 0 auto !important;text-align: center!important;padding:0 0 5px 10px!important;width:98%!important;border-bottom:1px solid #b6b6b6;border-right:none!important;}
             .height2{height:20px!important;}
             .font2{font-size:15px!important;}
             .height3{height: 15px!important;}
             .height4{height: 20px!important;}
             .width{width:135px !important;}

         }
         @media screen and (max-width: 499px) {

          .wrap000 {
              padding:0;
            }

             br{display:none;}
             .font1{line-height:22px!important;}
                          .wrap1003{display: inline-block; margin: 0 auto !important;text-align: center!important;padding:0 0 25px 0!important;width:100%!important;}
             .wrap1004{ margin: 0 auto !important;text-align: center!important;width:50%!important;display:inline-block;}
             .height1{height:6px!important;}

             .br1{display:block!important;}
             .font2{display: block !important; margin: 0 auto !important;text-align: center!important;padding:0 0 10px 0!important;}
             .font3{font-size:14px!important;}
              .wrap1006{ margin: 0 auto !important;text-align: center!important;width:100%!important;display:inline-block;padding:0 0 25px 0!important;}
             .height0{height:0!important;}
             .pad0{padding:0!important;}

         }
      </style>
   </head>
   <body style="padding:0;margin:0;" bgcolor="#eaeaea">
		<div itemscope itemtype="http://schema.org/EventReservation">
			  <meta itemprop="reservationNumber" content="<?php echo $booking->getId() ?>"/>
			  <link itemprop="reservationStatus" href="http://schema.org/<?php echo SLN_Enum_BookingStatus::getLabel($booking->getStatus()) ?>"/>
			  <div itemprop="underName" itemscope itemtype="http://schema.org/Person">
				<meta itemprop="name" content="<?php echo $booking->getDisplayName() ?>"/>
			  </div>
			  <div itemprop="reservationFor" itemscope itemtype="http://schema.org/Event">
				<meta itemprop="name" content="<?php echo $booking->getTitle() ?>"/>
				<meta itemprop="startDate" content="<?php echo $booking->getStartsAt()->format(DateTime::RFC3339) ?>"/>
				<?php
						if(preg_match("/(.+), (\w+), (\w+) (\w+) (\w+)/", $plugin->getSettings()->get('gen_address'), $matches)){
   						list($original, $street, $city, $state, $zip, $country) = $matches;
                  }else{
                     $original = $street = $city = $state = $zip = $country = '';
                  }
				?>
				<div itemprop="location" itemscope itemtype="http://schema.org/Place">
				  <meta itemprop="name" content="<?php echo $original ?>"/>
				  <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
					<meta itemprop="streetAddress" content="<?php echo $street ?>"/>
					<meta itemprop="addressLocality" content="<?php echo $city ?>"/>
					<meta itemprop="addressRegion" content="<?php echo $state ?>"/>
					<meta itemprop="postalCode" content="<?php echo $zip ?>"/>
					<meta itemprop="addressCountry" content="<?php echo $country ?>"/>
				  </div>
				</div>
			  </div>
		</div>


      <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eaeaea" class="wrap000">
         <tr>
            <td align="center" valign="top">
               <table width="640" border="0" cellspacing="0" cellpadding="0" class="wrap1001">
                  <tr>
                     <td align="left" valign="top" bgcolor="#ffffff">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                           <tr>
                              <td align="center" valign="top">
                                 <table width="605" cellspacing="0" cellpadding="0" border="0" class="wrap1002">
                                    <tr>
                                       <td align="center" valign="top" height="31" style="font-size:1px;line-height:1px;">&nbsp;</td>
                                    </tr>
                                      <!--1st blk starts here-->
                                    <tr>
                                       <td align="left" valign="top">
                                          <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                             <tr>
                                                <td align="center" valign="top">
                                                   <table width="550" cellspacing="0" cellpadding="0" border="0" class="wrap1001">

							<tr>
							   <td align="center" valign="top">
							      <table width="100%" cellspacing="0" cellpadding="0" border="0">

								 <tr>
								    <td align="left" valign="top" width="202" class="wrap1003">
								       <a href="#" target="_blank">

									    <?php $logo = $plugin->getSettings()->get('gen_logo');?>

									    <img src="<?php echo ($logo ? wp_get_attachment_image_url($logo, 'sln_gen_logo') : apply_filters('sln_default_email_logo', SLN_PLUGIN_URL . '/img/email/logo.png')); ?>" <?php echo ($logo ? '' : 'width="145" height="37"') ?> alt="img" border="0">

								       </a>
								    </td>
								    <td align="left" valign="top" width="199" class="wrap1004">
									<?php if ($contentTemplate !== '_follow_up_content'): ?>
									    <?php echo $plugin->loadView('mail/_booking_info', compact('booking')) ?>
									<?php endif;?>
								    </td>
								    <td align="left" valign="top" class="wrap1004">
									<?php echo $plugin->loadView('mail/_salon_info', compact('plugin')) ?>
								    </td>
								 </tr>

								 <tr>
								    <td align="center" valign="top" height="34" style="font-size:1px;line-height:1px;">&nbsp;</td>
								 </tr>
							      </table>
							   </td>
							</tr>

							<?php
                     if(!isset($updated_message)){
                        $updated_message = isset($data['updated_message']) ? $data['updated_message'] : '';
                     }
                     if(!isset($updated)){
                        $updated = isset($data['updated']) ? $data['updated'] : false;
                     }
                     if(!isset($remind)){
                        $remind = isset($data['remind']) ? $data['remind'] : false;
                     }
                     if(!isset($customer)){
                        $customer = $booking->getCustomer();
                     }
                     echo $plugin->loadView('mail/' . $contentTemplate, compact('booking', 'plugin', 'updated_message', 'customer', 'forAdmin', 'updated', 'remind')) ?>
                                                   </table>
                                                </td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
			        <?php if ($contentTemplate !== '_follow_up_content'): ?>
                                      <!--1st blk ends here-->
                                    <!--2nd blk starts here-->
				    <?php echo $plugin->loadView('mail/_summary_details', compact('booking', 'forAdmin')) ?>
                                    <!--2nd blk ends here-->
                                    <tr>
                                       <td align="center" valign="top" height="48" style="font-size:1px;line-height:1px;" class="height4">&nbsp;</td>
                                    </tr>

                                          <!--3rd blk starts here-->
                                    <tr>
                                       <td align="center" valign="top">
                                          <table width="540" cellspacing="0" cellpadding="0" border="0" class="wrap1001 ">
                                             <tr>
                                                <td align="left" valign="top" class="wrap1006">
						    <?php echo $plugin->loadView('mail/_customer_info', compact('booking')) ?>
                                                </td>
                                                <td align="left" valign="top" class="wrap1006">
						    <?php echo $plugin->loadView('mail/_custom_fields', compact('booking')) ?>
                                                </td>
                                                <td align="left" valign="top" class="wrap1006">
						    <?php if ($forAdmin): ?>
							<?php echo $plugin->loadView('mail/_admin_manage_buttons', compact('booking', 'plugin')) ?>
						    <?php elseif ($contentTemplate !== '_feedback_content'): ?>
							<?php echo $plugin->loadView('mail/_customer_manage_buttons', compact('booking', 'plugin', 'customer')) ?>
						    <?php endif;?>
                                                </td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td align="center" valign="top" height="31" style="font-size:1px;line-height:1px;" class="height0">&nbsp;</td>
                                    </tr>
                                    <!--3rd blk ends here-->

                                    <!--3rd blk ends here-->
                                    <!--4th blk starts here-->
            <?php if ($contentTemplate !== '_feedback_content') {
	?>
				    <?php if (in_array($booking->getStatus(), array(
		SLN_Enum_BookingStatus::PAID,
		SLN_Enum_BookingStatus::CONFIRMED,
		SLN_Enum_BookingStatus::PAY_LATER,
	))): ?>

					<?php echo $plugin->loadView('mail/_add_to_calendar', compact('booking', 'data')) ?>

				    <?php endif;?>

                                    <!--6th blk starts here-->
                                    <tr>
                                       <td align="center" valign="top" bgcolor="#9a9a9a">
                                          <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                             <tr>
                                                <td align="center" valign="top" height="30" style="font-size:1px;line-height:1px;" class="height4">&nbsp;</td>
                                             </tr>
                                             <tr>
                                                <td align="center" valign="top">
                                                   <table width="534" cellspacing="0" cellpadding="0" border="0" class="wrap1002">
                                                      <tr>
                                                         <td align="left" valign="top" style="font-size:14px;line-height:16px;color:#ffffff;font-weight:bold;font-family: 'Avenir-Medium',sans-serif,arial;">
							    <?php _e("Important notes", 'salon-booking-system')?>
                                                         </td>
                                                      </tr>
                                                      <tr>
                                                         <td align="center" valign="top" height="7" style="font-size:1px;line-height:1px;">&nbsp;</td>
                                                      </tr>
                                                      <tr>
                                                         <td align="left" valign="top" style="font-size:16px;line-height:20px;color:#ffffff;font-weight:500;font-family: 'Avenir-Medium',sans-serif,arial;">
                                                            <?php echo $plugin->getSettings()->get('gen_timetable') ?>
                                                         </td>
                                                      </tr>
                                                      <tr>
                                                         <td align="center" valign="top" height="24" style="font-size:1px;line-height:1px;">&nbsp;</td>
                                                      </tr>
                                                   </table>
                                                </td>
                                             </tr>
                                          </table>
                                       </td>
                                    </tr>
                                  <?php }?>
		                <?php endif;?>
                                    <tr>
                                       <?php if(defined('SLN_SPECIAL_EDITION') && SLN_SPECIAL_EDITION): ?>
                                       <td align="center" valign="top" height="21" style="color:#7d7d7d;font-size:0.87em;padding:10px;"><?php _e('Proudly powered by', 'salon-booking-system') ?> <a target="_blanck" style="color:#127dc0;font-size: 1.03em;font-weight: 800;" href="https://www.salonbookingsystem.com/plugin-pricing/#utm_source=plugin-credits&utm_medium=email-notification&utm_campaign=email-notification&utm_id=plugin-credits"><?php _e('Salon Booking System', 'salon-booking-system'); ?></a></td>
                                       <?php else: ?>
                                       <td align="center" valign="top" height="21" style="font-size:1px;line-height:1px;">&nbsp;</td>
                                       <?php endif; ?>
                                    </tr>
                                    <!--7th blk ends here-->
                                 </table>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>
   </body>
</html>