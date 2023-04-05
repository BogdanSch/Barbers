<?php

class SLN_Action_Ajax_RemoveDailyHolydays extends SLN_Action_Ajax_Abstract
{
	private $errors = array();

	public function execute()
	{
		if (!is_user_logged_in()) {
			return array( 'redirect' => wp_login_url());
		}


		$plugin = SLN_Plugin::getInstance();
		$settings = $plugin->getSettings();

		if(current_user_can('manage_salon')) {

                        $date             = sanitize_text_field(wp_unslash($_GET['date']));
                        $_assistants_mode = sanitize_text_field(wp_unslash($_GET['_assistants_mode']));
                        if ($_assistants_mode == "false") {
                            $holidays_rules = $settings->get('holidays_daily');
                            $search_rule=array();

                            foreach ($holidays_rules as $rule) {
                                    if(!(
                                            ($date === $rule['from_date'] ||
                                            $date === $rule['to_date']) &&
                                            $rule['daily'] === true
                                    )) $search_rule[] = $rule;
                            }

                            $settings->set('holidays_daily',$search_rule);
                            $settings->save();

                        } else {
                            $attendants = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT)->getAll();

                            foreach ($attendants as $attendant) {
                                $holidays_rules   = $attendant->getMeta('holidays_daily')?:array();
                                $search_rule=array();

                                foreach ($holidays_rules as $rule) {
                                        if(!(
                                                ($date === $rule['from_date'] ||
                                                $date === $rule['to_date']) &&
                                                $rule['daily'] === true
                                        )) $search_rule[] = $rule;
                                }
                                $attendant->setMeta('holidays_daily', $search_rule);
                            }
                        }

		} else {
			$this->addError(__("You don't have permissions", 'salon-booking-system'));
		}

		$search_rule = apply_filters('sln.get-day-holidays-rules', $settings->getDailyHolidayItems());

                $holidays_assistants_rules  = array();
                $assistants                 = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT)->getAll();

                foreach ($assistants as $att) {
                    $holidays_assistants_rules[$att->getId()] = $att->getMeta('holidays_daily')?:array();
                }

		$holidays_assistants_rules = apply_filters('sln.get-day-holidays-assistants-rules', $holidays_assistants_rules, $assistants);

		if ($errors = $this->getErrors()) {
			$ret = compact('errors');
		} else {
			$ret = array('success' => 1,'rules'=>$search_rule, 'assistants_rules' => $holidays_assistants_rules);
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

	public function removeCorruptedHoliday(){
		$plugin = SLN_Plugin::getInstance();
		$settings = $plugin->getSettings();
		$holidays_rules = $settings->get('holidays_daily');
		foreach ($holidays_rules as $index => $rule) {
			if(!empty($data['from_date']) && !empty($data['to_date']) && !empty($data['from_time']) && !empty($data['to_time']) ){}else{
				unset($holidays_rules[$index]);
			}
		}
		$settings->set('holidays_daily',$holidays_rules);
		$settings->save();
		$bc = $plugin->getBookingCache();
		$bc->refreshAll();
	}
}
