<?php

class SLN_Action_Sms_Ip1SmsWebservice extends SLN_Action_Sms_Abstract
{
    const API_URL = 'https://web.smscom.se/sendsms/sendsms.asmx?wsdl';
    public function send($to, $message, $sms_prefix = '')
    {
        $to = $this->processTo($to, $sms_prefix);
        $client = new SoapClient(self::API_URL);
        $ret = $client->sms(array(
            'konto' => $this->plugin->getSettings()->get('sms_account'),
            'passwd' => $this->plugin->getSettings()->get('sms_password'),
            'till' => $to,
            'from' => $this->plugin->getSettings()->get('sms_from'),
            'meddelande' => $message,
            'prio' => 1
        ));
    }
}
