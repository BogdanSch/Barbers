<?php

class SLN_Service_Sms
{
    private $plugin;
    private $exception;

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function send($number, $message, $sms_prefix = '')
    {
        try {
            $provider = SLN_Enum_SmsProvider::getService(
                $this->plugin->getSettings()->get('sms_provider'),
                $this->plugin
            );
            $provider->send($number, trim($message), $sms_prefix);
        } catch (SLN_Action_Sms_Exception $e) {
            $this->exception = $e;
        }
    }

    public function hasError()
    {
        return isset($this->exception);
    }

    public function getError()
    {
        return $this->getException()->getMessage();
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    public function clearError()
    {
        $this->exception = null;
    }
}