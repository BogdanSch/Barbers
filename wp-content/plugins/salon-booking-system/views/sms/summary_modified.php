<?php
/**
 * @var SLN_Plugin          $plugin
 * @var SLN_Wrapper_Booking $booking
 */

$default_template = SLN_Admin_SettingTabs_GeneralTab::getDefaultSmsNotificationMessageModified();
$template	  = $plugin->getSettings()->get('sms_notification_message_modified') ? $plugin->getSettings()->get('sms_notification_message_modified') : $default_template;

$bookingDateTime = $plugin->getSettings()->isDisplaySlotsCustomerTimezone() && $booking->getCustomerTimezone() ? (new SLN_DateTime($booking->getDate()->format('Y-m-d') . ' ' . $booking->getTime()->format('H:i')))->setTimezone(new DateTimeZone($booking->getCustomerTimezone())) : new SLN_DateTime($booking->getDate()->format('Y-m-d') . ' ' . $booking->getTime()->format('H:i'));

$message = str_replace(
    array(
	'[NAME]',
	'[SALON NAME]',
	'[DATE]',
	'[TIME]',
	'[PRICE]',
	'[BOOKING ID]',
    ),
    array(
	$booking->getDisplayName(),
	$plugin->getSettings()->getSalonName(),
	$plugin->format()->date($bookingDateTime),
	$plugin->format()->time($bookingDateTime),
	$booking->getAmount(),
	$booking->getId(),
    ),
    $template
);

if (strlen($message) > 160) {
    $more_string = __('...more details in the email confirmation', 'salon-booking-system');
    $message	 = substr($message, 0, ( 159 - strlen($more_string))) . $more_string;
}

echo $message;