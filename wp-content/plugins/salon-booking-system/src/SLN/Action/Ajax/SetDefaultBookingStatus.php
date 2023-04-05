<?php // algolplus

class SLN_Action_Ajax_SetDefaultBookingStatus extends SLN_Action_Ajax_Abstract
{
	private $errors = array();

	public function execute()
	{
		if (!is_user_logged_in()) {
			return array( 'redirect' => wp_login_url());
		}
        
        $settings = SLN_Plugin::getInstance()->getSettings();
        
		$settings->setDefaultBookingStatus($_POST['status']);
        $settings->save();

		return array('success' => 1);
	}
}
