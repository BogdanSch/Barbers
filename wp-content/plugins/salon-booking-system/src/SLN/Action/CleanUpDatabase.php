<?php

class SLN_Action_CleanUpDatabase
{
    /** @var SLN_Plugin */
    private $plugin;

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function execute()
    {
	$now	    = new SLN_DateTime();
	$settings   = $this->plugin->getSettings();

	$holidays_rules  = $settings->get('holidays_daily') ?: array();
	$_holidays_rules = array();

	foreach ($holidays_rules as $holiday_rule) {
	    if (($now->getTimestamp() - (new SLN_DateTime($holiday_rule['to_date'].' '.$holiday_rule['to_time']))->getTimestamp()) / (24 * 3600) < 7) {
		$_holidays_rules[] = $holiday_rule;
	    }
	}

	$settings->set('holidays_daily', $_holidays_rules);

	$holidays  = $settings->get('holidays');
	$_holidays = array();

	foreach ($holidays as $holiday) {
	    if ($now->getTimestamp() - (new SLN_DateTime($holiday['to_date'].' '.$holiday['to_time']))->getTimestamp() < 0) {
		$_holidays[] = $holiday;
	    }
	}

	$settings->set('holidays', $_holidays);

	$settings->save();

        $attendants = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT)->getAll();

        foreach ($attendants as $attendant) {
            $holidays_rules  = $attendant->getMeta('holidays_daily') ?: array();
            $_holidays_rules = array();

            foreach ($holidays_rules as $holiday_rule) {
                if (($now->getTimestamp() - (new SLN_DateTime($holiday_rule['to_date'].' '.$holiday_rule['to_time']))->getTimestamp()) / (24 * 3600) < 30) {
                    $_holidays_rules[] = $holiday_rule;
                }
            }

            $attendant->setMeta('holidays_daily', $_holidays_rules);
        }
    }

}