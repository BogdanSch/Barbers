<?php

class SLN_Shortcode_Salon_DateStep extends SLN_Shortcode_Salon_Step
{

    protected function dispatchForm()
    {
        $bb     = $this->getPlugin()->getBookingBuilder();
        if(isset($_POST['sln'])){
                $date   = SLN_Func::filter(sanitize_text_field( wp_unslash( $_POST['sln']['date']  ) ), 'date');
                $time   = SLN_Func::filter(sanitize_text_field( wp_unslash( $_POST['sln']['time']  ) ), 'time');
                $timezone = SLN_Func::filter(sanitize_text_field( wp_unslash( $_POST['sln']['customer_timezone']  ) ), '');
        }
        if ($this->getPlugin()->getSettings()->isDisplaySlotsCustomerTimezone() && $timezone) {
            $dateTime = (new SLN_DateTime(SLN_Func::filter($date, 'date') . ' ' . SLN_Func::filter($time, 'time'.':00'), new DateTimeZone($timezone)))->setTimezone(SLN_DateTime::getWpTimezone());
            $date = SLN_Func::filter($this->getPlugin()->format()->date($dateTime), 'date');
            $time = SLN_Func::filter($this->getPlugin()->format()->time($dateTime), 'time');
        }
        $bb
            ->removeLastId()
            ->setDate($date)
            ->setTime($time)
            ->setCustomerTimezone($timezone);
        $obj = new SLN_Action_Ajax_CheckDate($this->getPlugin());
        $obj
            ->setDate($date)
            ->setTime($time)
            ->execute();
        foreach ($obj->getErrors() as $err) {
            $this->addError($err);
        }
        if (!$this->getErrors()) {
            $bb->save();

            return true;
        }
    }


}
