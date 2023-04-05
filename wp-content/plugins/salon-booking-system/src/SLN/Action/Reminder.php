<?php

class SLN_Action_Reminder
{
    const EMAIL = 'email';
    const SMS = 'sms';

    /** @var SLN_Plugin */
    private $plugin;
    private $mode;

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function executeSms()
    {
        $this->mode = self::SMS;

        return $this->execute();
    }

    public function executeEmail()
    {
        $this->mode = self::EMAIL;

        return $this->execute();
    }

    private function execute()
    {
        SLN_TimeFunc::startRealTimezone();

        $type = $this->mode;
        $p = $this->plugin;
        $remind = $p->getSettings()->get($type.'_remind');
        if ($remind) {
            $p->addLog($type.' reminder execution');
            if (self::SMS === $type && SLN_Enum_CheckoutFields::getField('phone')->isHiddenOrNotRequired()) {
                $p->addLog($type.' phone field is hidden or not required');
                foreach ($this->getBookings() as $booking) {
                    $booking->setMeta($type.'_remind', false);
                    $booking->setMeta($type.'_remind_error', $type.' phone field is hidden or not required');
                }
            }
            else {
                foreach ($this->getBookings() as $booking) {
                    try {
                        $this->send($booking);

						//succeeded email reminders will be logged through hooks
						if ($this->mode === self::SMS) {
							$booking->setMeta($type.'_remind', true);
							$booking->setMeta($type.'_remind_utc_time', (new SLN_DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
						}
                    } catch (Exception $ex) {
                        $booking->setMeta($type.'_remind', false);
                        $booking->setMeta($type.'_remind_error', $ex->getMessage());
                    }
                }
            }
            $p->addLog($type.' reminder execution ended');
        }

        SLN_TimeFunc::endRealTimezone();
    }

    /**
     * @param SLN_Wrapper_Booking $booking
     * @throws Exception
     */
    private function send(SLN_Wrapper_Booking $booking)
    {
        $p = $this->plugin;
        if (self::EMAIL == $this->mode) {
	        /*add_action('wp_mail_succeeded', function ($data) use ($p) {
		        $headers = $data['headers'];
		        if ($headers['remind']) {
			        $bookingId = intval($headers['booking-id']);
			        $booking = $p->createBooking($bookingId);
			        $p->addLog('email reminder sent to '.$booking->getId());
			        $booking->setMeta('email_remind', true);
			        $booking->setMeta('email_remind_utc_time', (new SLN_DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
		        }
	        });*/

            $p->addLog('email reminder started to be sent to '.$booking->getId());
            $booking->setMeta('email_remind', true);
            $booking->setMeta('email_remind_utc_time', (new SLN_DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));

	        add_action('wp_mail_failed', function (WP_Error $error) use ($p) {
		        $data = $error->get_error_data();
		        $headers = $data['headers'];
		        if ($headers['remind']) {
			        $bookingId = intval($headers['booking-id']);
			        $booking = $p->createBooking($bookingId);
                    $p->addLog('email reminder for '.$booking->getId(). ' failed with: ' . $error->get_error_message());
			        $booking->setMeta('email_remind', false);
                    $booking->setMeta('email_remind_utc_time', '');
			        $booking->setMeta('email_remind_error', $error->get_error_message());
		        }
	        });

            $args = compact('booking');
            $args['remind'] = true;
            $p->sendMail('mail/summary', $args);
        } else {
            $p->sms()->clearError();
            $p->sms()->send(
                $booking->getPhone(),
                $p->loadView('sms/remind', compact('booking')),
	            $booking->getMeta('sms_prefix')
            );
            if ($p->sms()->hasError()) {
                throw new Exception($p->sms()->getError());
            }
        }
    }

    /**
     * @return SLN_Wrapper_Booking[]
     * @throws Exception
     */
    private function getBookings()
    {
        $min = $this->getMin();
        $max = $this->getMax();

        $statuses = array(SLN_Enum_BookingStatus::PAID, SLN_Enum_BookingStatus::CONFIRMED, SLN_Enum_BookingStatus::PAY_LATER);

        /** @var SLN_Repository_BookingRepository $repo */
        $repo = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_BOOKING);
        $tmp = $repo->get(
            array(
                'post_status' => $statuses,
                'day@min'     => $min,
                'day@max'     => $max
            )
        );
        $ret = array();
        foreach ($tmp as $booking) {
            $d = $booking->getStartsAt();
            $done = $booking->getMeta($this->mode.'_remind');
            if ($d >= $min && $d <= $max && !$done) {
                $ret[] = $booking;
            }
        }

        return $ret;
    }


    /**
     * @return DateTime
     */
    private function getMin()
    {
        return new SLN_DateTime();
    }

    /**
     * @return DateTime
     */
    private function getMax()
    {
        $interval = $this->plugin->getSettings()->get($this->mode.'_remind_interval');
        $date = new SLN_DateTime();
        $date->modify($interval);

        return $date;
    }
}
