<?php
/**
 * @var SLN_Plugin          $plugin
 * @var SLN_Wrapper_Booking $booking
 */

$template = $plugin->getSettings()->get('sms_notification_message');

$bookingDateTime = $plugin->getSettings()->isDisplaySlotsCustomerTimezone() && $booking->getCustomerTimezone() ? (new SLN_DateTime($booking->getDate()->format('Y-m-d') . ' ' . $booking->getTime()->format('H:i')))->setTimezone(new DateTimeZone($booking->getCustomerTimezone())) : new SLN_DateTime($booking->getDate()->format('Y-m-d') . ' ' . $booking->getTime()->format('H:i'));

if ($template) {

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
	$message     = substr($message, 0, ( 159 - strlen($more_string))) . $more_string;
    }

    echo $message;

    return;
}

$message =
__('Hi','salon-booking-system') .' ' . $booking->getFirstname() . ' ' . $booking->getLastname()

. ' ' . __('don\'t forget your reservation at','salon-booking-system').' '. $plugin->getSettings()->getSalonName()
. ' ' . __('on','salon-booking-system').' '. $plugin->format()->date($bookingDateTime)
. ' ' . __('at','salon-booking-system').' '. $plugin->format()->time($bookingDateTime)
. ' ' . __('| Booking ID ','salon-booking-system') .$booking->getId()
. ' ' . __('| Timing: ','salon-booking-system') .' ';
foreach($booking->getBookingServices()->getItems() as $bookingService){
        $bookingServiceStartsAt = $plugin->getSettings()->isDisplaySlotsCustomerTimezone() && $booking->getCustomerTimezone() ? $bookingService->getStartsAt()->setTimezone(new DateTimeZone($booking->getCustomerTimezone())) : $bookingService->getStartsAt();
	$message .=  $bookingServiceStartsAt->format( 'H:i' )
	.' '.  (($attendant = $bookingService->getAttendant()) ?
					(!is_array($attendant) ?
						$attendant->getTitle() :
						SLN_Wrapper_Attendant::implodeArrayAttendantsName(', ', $attendant)) :
					$bookingService->getService()->getTitle()).' ';
}
$message .= __('Price','salon-booking-system') .': '. $booking->getAmount();
if(strlen($message)>160){
	$more_string = __('...more details in the email confirmation','salon-booking-system');
	$message = substr($message, 0, ( 159 - strlen($more_string))).$more_string;
}
echo $message;