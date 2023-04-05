<?php
/**
 * @var SLN_Plugin          $plugin
 * @var SLN_Wrapper_Booking $booking
 */
if(empty($data['to'])){
    $data['to']      = $booking->getEmail();
}
$bookingDateTime = $plugin->getSettings()->isDisplaySlotsCustomerTimezone() && $booking->getCustomerTimezone() ? (new SLN_DateTime($booking->getDate()->format('Y-m-d') . ' ' . $booking->getTime()->format('H:i')))->setTimezone(new DateTimeZone($booking->getCustomerTimezone())) : new SLN_DateTime($booking->getDate()->format('Y-m-d') . ' ' . $booking->getTime()->format('H:i'));

if(isset($remind) && $remind) {
    $data['subject'] = str_replace(
        array(
            '[DATE]',
            '[TIME]',
            '[SALON NAME]'
        ),
        array(
            $plugin->format()->date($bookingDateTime),
            $plugin->format()->time($bookingDateTime),
            $plugin->getSettings()->get('gen_name') ?
                $plugin->getSettings()->get('gen_name') : get_bloginfo('name')
        ),
        $plugin->getSettings()->get('email_subject')
    );
    $manageBookingsLink = true;
} elseif(isset($updated) && $updated) {
    $data['subject'] = str_replace(
        '[SALON NAME]',
        $plugin->getSettings()->get('gen_name') ?
            $plugin->getSettings()->get('gen_name') : get_bloginfo('name'),
        __('Your reservation at [SALON NAME] has been modified', 'salon-booking-system')
    );
    $manageBookingsLink = true;
} elseif(isset($rescheduled) && $rescheduled) {
    $current_user = wp_get_current_user();
    $data['subject'] = sprintf(
        __('Booking #%s has been re-scheduled by %s', 'salon-booking-system'),
        $booking->getId(),
        implode(' ', array_filter(array($current_user->user_firstname, $current_user->user_lastname)))
    );
    $manageBookingsLink = true;
} else {
    $data['subject'] = str_replace(
        array(
            '[DATE]',
            '[TIME]',
            '[SALON NAME]'
        ),
        array(
            $plugin->format()->date($bookingDateTime),
            $plugin->format()->time($bookingDateTime),
            $plugin->getSettings()->get('gen_name') ?
                $plugin->getSettings()->get('gen_name') : get_bloginfo('name')
        ),
        $plugin->getSettings()->get('email_nb_subject')
    );

    $data['subject'] = apply_filters('sln.new_booking.notifications.email.subject', $data['subject'], $booking);

    $manageBookingsLink = true;
}
$forAdmin = false;

$contentTemplate = '_summary_content';

if(!isset($remind)){
    $remind = false;
}
$rescheduled = empty($rescheduled) ? false : $rescheduled;
$forAdmin = empty($forAdmin) ? null : $forAdmin;
$updated = empty($updated) ? false : $updated;
echo $plugin->loadView('mail/template', compact('booking', 'plugin', 'data', 'remind', 'bookingDateTime', 'manageBookingsLink', 'updated', 'rescheduled', 'forAdmin', 'contentTemplate'));