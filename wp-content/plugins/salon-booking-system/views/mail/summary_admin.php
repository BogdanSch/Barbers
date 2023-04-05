<?php
/**
 * @var SLN_Plugin $plugin
 * @var SLN_Wrapper_Booking $booking
 */
$recipients = array();

$adminEmail           = $plugin->getSettings()->getSalonEmail();
$attendantEmailOption = $plugin->getSettings()->get('attendant_email');

if(isset($updated) && $updated) {
    if ($attendantEmailOption) {
        $bookingAttendants = $booking->getAttendants();
        if (!empty($bookingAttendants)) {
            foreach($bookingAttendants as $attendant) {
                if(!is_array($attendant)){
                    $recipients[] = $attendant->getEmail();
                }else{
                    $recipients = arrya_merge($recipients, SLN_Wrapper_Attendant::getArrayAttendantsValue('getEmail', $attendant));
                }
            }
        }
    }
    $recipients = array_unique(array_filter($recipients));

    if (empty($recipients) && $sendToAdmin) {
        $recipients[] = $adminEmail;
    }

    $data['to'] = implode(',', $recipients);
    $data['subject'] = __('Reservation has been modified ','salon-booking-system')
                       . $plugin->format()->date($booking->getDate())
                       . ' - ' . $plugin->format()->time($booking->getTime());
} elseif(isset($rescheduled) && $rescheduled) {
    $data['to'] = $adminEmail;
        if ($attendantEmailOption
        && ($attendants = $booking->getAttendants(true))

    ) {
        foreach ($attendants as $attendant) {
            if(!is_array($attendant)){
                if (($email = $attendant->getEmail())){
                    if(!is_array($data['to'])) $data['to'] = array_filter(array(isset($data['to']) ? $data['to'] : '', $email));
                    else $data['to'][] = $email;
                }
            }else{
                foreach($attendant as $att){
                    if(($email = $att->getEmail())){
                        if(!is_array($data['to'])){
                            $data['to'] = array_filter(array(isset($data['to']) ? $data['to'] : '', $email));
                        }else{
                            $data['to'][] = $email;
                        }
                    }
                }
            }
        }

    }
    $current_user = wp_get_current_user();
    $data['subject'] = sprintf(
        __('Booking #%s has been re-scheduled by %s', 'salon-booking-system'),
        $booking->getId(),
        implode(' ', array_filter(array($current_user->user_firstname, $current_user->user_lastname)))
    );
} else {
    if ($sendToAdmin) {
        $data['to'] = $adminEmail;
    }
        if ($attendantEmailOption
        && ($attendants = $booking->getAttendants(true))

    ) {
        foreach ($attendants as $attendant) {
            if(!is_array($attendant)){
                if (($email = $attendant->getEmail())){
                    if(!is_array($data['to'])) $data['to'] = array_filter(array(isset($data['to']) ? $data['to'] : '', $email));
                    else $data['to'][] = $email;
                }
            }else{
                foreach($attendant as $att){
                    if(($email = $att->getEmail())){
                        if(!is_array($data['to'])){
                            $data['to'] = array_filter(array(isset($data['to']) ? $data['to'] : '', $email));
                        }else{
                            $data['to'][] = $email;
                        }
                    }
                }
            }
        }

    }
    $data['subject'] = __('New booking for ','salon-booking-system')
                       . $plugin->format()->date($booking->getDate())
                       . ' - ' . $plugin->format()->time($booking->getTime());

    $data['subject'] = apply_filters('sln.new_booking.notifications.email.subject', $data['subject'], $booking);

    $data['headers'] = array(
        'Reply-To: '. $booking->getDisplayName() .' <'. $booking->getEmail() .'>',
    );
}
$forAdmin = true;

$contentTemplate = '_summary_content';

echo $plugin->loadView('mail/template', compact('booking', 'plugin', 'data', 'updated', 'rescheduled', 'forAdmin', 'contentTemplate'));
