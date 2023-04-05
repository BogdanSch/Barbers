<?php

abstract class SLN_Action_Sms_Abstract
{
    protected $plugin;
    protected $serviceKey;
    protected $serviceLabel;

    public function __construct(SLN_Plugin $plugin, $serviceKey, $serviceLabel)
    {
        $this->plugin	    = $plugin;
	$this->serviceKey   = $serviceKey;
	$this->serviceLabel = $serviceLabel;
    }

    abstract public function send($to, $message, $sms_prefix = '');

    protected function getAccount()
    {
        return $this->plugin->getSettings()->get('sms_account');
    }

    protected function getPassword()
    {
        return $this->plugin->getSettings()->get('sms_password');
    }

    protected function getFrom()
    {
        return $this->plugin->getSettings()->get('sms_from');
    }

    protected function processTo($to, $sms_prefix = '')
    {
        return $this->plugin->format()->phone($to, $sms_prefix);
    }

    protected function createException($message, $code = 1000, $previous = null)
    {
        throw new SLN_Action_Sms_Exception($message, $code, $previous);
    }

    public function getServiceKey()
    {
        return $this->serviceKey;
    }

    public function getServiceLabel()
    {
        return $this->serviceLabel;
    }

    public function renderSettingsFields($data) {}

    public function getFields()
    {
	return array();
    }

}
