<?php

class SLN_Service_Messages
{
    private $plugin;
    private $disabled = false;
    private $sendToAdmin = true;
    private $sendToCustomer = true;

    private static $statusForSummary = array(
        SLN_Enum_BookingStatus::PAID,
        SLN_Enum_BookingStatus::PAY_LATER,
    );

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function setDisabled($bool)
    {
        $this->disabled = $bool;
    }

    public function setSendToAdmin($bool)
    {
        $this->sendToAdmin = $bool;
    }

    public function setSendToCustomer($bool)
    {
        $this->sendToCustomer = $bool;
    }

    public function sendByStatus(SLN_Wrapper_Booking $booking, $status)
    {
        if ($this->disabled) {
            return;
        }

	if ($booking->getMeta('disable_status_change_email')) {
	    $booking->setMeta('disable_status_change_email', 0);
	    return;
	}

        do_action('sln.messages.before_booking_send_message', $booking);
        $p = $this->plugin;
        $sendToAdmin = $this->sendToAdmin;
        $sendToCustomer = $this->sendToCustomer;
        if ($status == SLN_Enum_BookingStatus::CONFIRMED) {
            $this->sendBookingConfirmed($booking, $sendToAdmin, $sendToCustomer);
        } elseif ($status == SLN_Enum_BookingStatus::CANCELED) {
            if($booking->getNotifyCustomer() && $sendToCustomer) {
                $p->sendMail('mail/status_canceled', compact('booking'));
            }
            $forAdmin = true;
            $p->sendMail('mail/status_canceled', compact('booking', 'forAdmin', 'sendToAdmin'));
        } elseif ($status == SLN_Enum_BookingStatus::PENDING_PAYMENT && $booking->getNotifyCustomer() && $sendToCustomer) {
            $p->sendMail('mail/status_pending_payment', compact('booking'));
        } elseif (in_array($status, self::$statusForSummary)) {
            $this->sendSummaryMail($booking, $sendToAdmin, $sendToCustomer);
            $this->sendSmsBooking($booking, $sendToAdmin, $sendToCustomer);
        }
    }

    private function sendBookingConfirmed(SLN_Wrapper_Booking $booking, $sendToAdmin = true, $sendToCustomer = true)
    {
        if ($this->plugin->getSettings()->get('confirmation')) {
            $this->plugin->sendMail('mail/status_confirmed', compact('booking', 'sendToAdmin', 'sendToCustomer'));
        } else {
            $this->sendSummaryMail($booking, $sendToAdmin, $sendToCustomer);
        }
        $this->sendSmsBooking($booking, $sendToAdmin, $sendToCustomer);
    }

    public function sendBookingModified(SLN_Wrapper_Booking $booking) {
        $this->sendSmsModifiedBooking($booking);
        $this->sendSummaryModifiedMail($booking);
    }

    public function sendSmsBooking($booking, $sendToAdmin = true, $sendToCustomer = true)
    {
        do_action('sln.messages.before_booking_send_message', $booking);

        $p   = $this->plugin;
        $sms = $p->sms();
        $s   = $p->getSettings();

        if ($s->get('sms_new')) {

            $phone = $s->get('sms_new_number');
            if ($phone) {
                $sms->send($phone, $p->loadView('sms/summary', compact('booking')));
            }

            $phone = $booking->getPhone();
            if ($phone && $booking->getNotifyCustomer() && $sendToCustomer) {

                $sms->send($phone, $p->loadView('sms/summary', compact('booking')), $booking->getSmsPrefix());
            }
        }

        if ($s->get('sms_new_attendant')) {

            $tmpAttendants = $booking->getAttendants();
            $tmpAttendants = $tmpAttendants && is_array($tmpAttendants) ? $tmpAttendants : array();

            $attendants = array();

            foreach ($tmpAttendants as $a) {
                $attendants[$a->getId()] = $a;
            }

            foreach ($attendants as $attendant) {

                $phone = $attendant->getPhone();

                if ($phone) {
                    $sms->send($phone, $p->loadView('sms/summary', compact('booking')), $attendant->getSmsPrefix());
                }
            }
        }

        do_action('sln.messages.booking_sms',$booking);
    }

    private function sendSmsModifiedBooking($booking) {
        do_action('sln.messages.before_booking_send_message', $booking);

        $p   = $this->plugin;
        $sms = $p->sms();
        $s   = $p->getSettings();

        if ($s->get('sms_modified')) {

            $phone = $s->get('sms_new_number');
            if ($phone) {
                $sms->send($phone, $p->loadView('sms/summary_modified', compact('booking')));
            }

            $phone = $booking->getPhone();
            if ($phone && $booking->getNotifyCustomer()) {
                $sms->send($phone, $p->loadView('sms/summary_modified', compact('booking')), $booking->getSmsPrefix());
            }
        }

        if ($s->get('sms_modified_attendant')) {

            $tmpAttendants = $booking->getAttendants();
            $tmpAttendants = $tmpAttendants && is_array($tmpAttendants) ? $tmpAttendants : array();

            $attendants = array();

            foreach ($tmpAttendants as $a) {
                $attendants[$a->getId()] = $a;
            }

            foreach ($attendants as $attendant) {

                $phone = $attendant->getPhone();

                if ($phone) {
                    $sms->send($phone, $p->loadView('sms/summary_modified', compact('booking')), $attendant->getSmsPrefix());
                }
            }
        }

        do_action('sln.messages.modified_booking_sms',$booking);
    }

    public function sendRescheduledMail($booking)
    {
        do_action('sln.messages.before_booking_send_message', $booking);

	    $rescheduled = true;
        $updated = false;

        $p = $this->plugin;
        if($booking->getNotifyCustomer()) {
            $p->sendMail('mail/summary', compact('booking', 'rescheduled', 'updated'));
        }
        $p->sendMail('mail/summary_admin', compact('booking', 'rescheduled', 'updated'));
    }

    public function sendSummaryMail($booking, $sendToAdmin = true, $sendToCustomer = true)
    {
        do_action('sln.messages.before_booking_send_message', $booking);
        $updated = false;
        $rescheduled = false;

        $p = $this->plugin;
        if($booking->getNotifyCustomer() && $sendToCustomer) {
            $p->sendMail('mail/summary', compact('booking', 'updated', 'rescheduled'));
        }
        $p->sendMail('mail/summary_admin', compact('booking', 'sendToAdmin', 'updated', 'rescheduled'));
        do_action('sln.messages.booking_summary_mail',$booking);
    }

    private function sendSummaryModifiedMail($booking) {
        do_action('sln.messages.before_booking_send_message', $booking);

        $p = $this->plugin;
        $updated = true;
        $rescheduled = false;
        if($booking->getNotifyCustomer()) {
            $p->sendMail('mail/summary', compact('booking', 'updated', 'rescheduled'));
        }
        $p->sendMail('mail/summary_admin', compact('booking', 'updated', 'rescheduled'));
        do_action('sln.messages.booking_summary_modified_mail',$booking);
    }

    public function getStatusForSummary() {
        return self::$statusForSummary;
    }
}
