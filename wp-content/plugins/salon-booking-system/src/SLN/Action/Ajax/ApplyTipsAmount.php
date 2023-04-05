<?php

class SLN_Action_Ajax_ApplyTipsAmount extends SLN_Action_Ajax_Abstract
{
	protected $date;
	protected $time;
	protected $errors = array();

	public function execute()
	{
	    $plugin = $this->plugin;
	    $tips   = sanitize_text_field(wp_unslash($_POST['sln']['tips']));

	    if ( ! is_numeric($tips) || floatval($tips) < 0.0 ) {
		$this->addError(__('Tips is not valid', 'salon-booking-system'));
	    } else {
		$tips = floatval($tips);
		$bb   = $plugin->getBookingBuilder();
		$bb->addTips($tips);
		$bb->save();
		$tipsValue = $bb->getTips();
	    }

	    if ($errors = $this->getErrors()) {
		$ret = compact('errors');
	    } else {
		$ret = array(
		    'success'  => 1,
		    'tips'     => $plugin->format()->money($tipsValue, false, false, true),
		    'total'    => $plugin->format()->money($bb->getTotal(), false, false, true),
		    'errors'   => array(
			__('Tips was applied', 'salon-booking-system')
		    )
		);
	    }

	    return $ret;
	}

	protected function addError($err)
	{
		$this->errors[] = $err;
	}

	public function getErrors()
	{
		return $this->errors;
	}
}