<?php

class SLN_Action_Sms_Plivo extends SLN_Action_Sms_Abstract
{

    public function send($to, $message, $sms_prefix = '')
    {

        require_once '_plivo.php';
        try {
            $p = new RestAPI($this->getAccount(), $this->getPassword());
            $to = $this->processTo($to, $sms_prefix);
            $params = array(
                'src' => str_replace('+', '', $this->getFrom()),
                'dst' => $to,
                'text' => $message,
                'type' => 'sms',
            );
            $response = @$p->send_message($params);
            $tmp = array_values($response);
            if (array_shift($tmp) != "202") {
                $this->createException(__('Plivo: Please check your settings', 'salon-booking-system'));
            }
        }catch(PlivoError $exception){
            $this->createException('Plivo: '.$exception->getMessage());
        }
    }
}
