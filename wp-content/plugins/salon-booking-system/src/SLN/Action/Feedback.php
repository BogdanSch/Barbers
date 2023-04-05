<?php

class SLN_Action_Feedback
{
    /** @var SLN_Plugin */
    private $plugin;
    private $mode;
    private $interval = '+1 days';

    public function __construct(SLN_Plugin $plugin) {
        $this->plugin = $plugin;
    }
    
    public function execute() {
        SLN_TimeFunc::startRealTimezone();

        $type = $this->mode;
        $p = $this->plugin;
        $feedback_reminder_mail = $p->getSettings()->get( 'feedback_email' );
        $feedback_reminder_sms = $p->getSettings()->get( 'feedback_sms' );
        if ($feedback_reminder_mail || $feedback_reminder_sms) {
            $p->addLog( 'feedback reminder execution' );
            foreach ( $this->getBookings() as $booking ) {
                if($feedback_reminder_mail) $this->sendMail( $booking );
                if($feedback_reminder_sms) $this->sendSms( $booking );
                $p->addLog( 'feedback reminder sent to ' . $booking->getId() );
                $booking->setMeta('feedback', true);
            }

            $p->addLog( 'feedback reminder execution ended' );
        }

        SLN_TimeFunc::endRealTimezone();
    }

    /**
     * @return SLN_Wrapper_Booking[]
     * @throws Exception
     */
    private function getBookings() {
        $current_day = new SLN_DateTime( '- 1 day' );

        $statuses = array( SLN_Enum_BookingStatus::PAID, SLN_Enum_BookingStatus::CONFIRMED, SLN_Enum_BookingStatus::PAY_LATER );

        /** @var SLN_Repository_BookingRepository $repo */
        $repo = $this->plugin->getRepository( SLN_Plugin::POST_TYPE_BOOKING );
        $tmp = $repo->get(
            array(
                'post_status' => $statuses,
                'day'         => $current_day,
            )
        );
        $ret = array();
        foreach ( $tmp as $booking ) {
            $done = $booking->getMeta('feedback');

            if ( !$done && SLN_Wrapper_Customer::isCustomer( $booking->getUserId() ) ) {
                $ret[] = $booking;
            }
        }
        return $ret;
    }

    private function sendSms( $booking ) {
        $p = $this->plugin;
        $p->sms()->send(
            $booking->getPhone(),
            $p->loadView('sms/feedback', compact('booking'))
        );
    }

    private function sendMail( $booking ) {
        $p = $this->plugin;
        $p->sendMail('mail/feedback', compact('booking'));
    }
}