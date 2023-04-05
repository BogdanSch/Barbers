<?php

class SLN_Action_Sms_Fake extends SLN_Action_Sms_Abstract
{
    public function send($to, $message, $sms_prefix = '')
    {
        $message = print_r(array($to, $message),true);
        $headers = 'From: '.SLN_Plugin::getInstance()->getSettings()->getSalonName().' <'.SLN_Plugin::getInstance()->getSettings()->getSalonEmail().'>'."\r\n";
        wp_mail(SLN_Plugin::getInstance()->getSettings()->getSalonEmail(), 'sms verification', $message, $headers);
    }
}
