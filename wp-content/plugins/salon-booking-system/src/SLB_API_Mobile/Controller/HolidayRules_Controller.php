<?php

namespace SLB_API_Mobile\Controller;

use SLN_Plugin;
use WP_REST_Server;
use SLN_DateTime;
use Salon\Util\Date;
use SLN_Enum_BookingStatus;
use DateTime;
use SLN_Formatter;

class HolidayRules_Controller extends REST_Controller
{
    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'holiday-rules';

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_holiday_rules'),
		'permission_callback' => '__return_true',
		'args' => apply_filters('sln_api_holiday_rules_register_routes_get_holiday_rules_args', array(
                	'date'     => array(
                    		'description'       => __('Date.', 'salon-booking-system'),
                    		'type'              => 'string',
                    		'format'            => 'YYYY-MM-DD',
                    		'required'          => false,
		    		'default'           => '',
                    		'validate_callback' => array($this, 'rest_validate_request_arg'),
           		),
            	)),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_holiday_rule' ),
                'permission_callback' => array( $this, 'create_holiday_rule_permissions_check' ),
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ),
	    array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_holiday_rule' ),
                'permission_callback' => array( $this, 'delete_holiday_rule_permissions_check' ),
		'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }

    public function create_holiday_rule_permissions_check( $request )
    {
        return current_user_can('manage_salon');
    }

    public function delete_holiday_rule_permissions_check( $request )
    {
        return current_user_can('manage_salon');
    }

    public function get_holiday_rules( $request )
    {
        try {

            do_action('sln_api_holiday_rules_get_holiday_rules_before', $request);

            do_action('sln_api_holiday_rules_get_holiday_rules_before_check', $request);

            $date = sanitize_text_field( wp_unslash( $request->get_param('date') ) );

            $plugin   = SLN_Plugin::getInstance();
            $settings = $plugin->getSettings();
            $formatter = new SLN_Formatter($plugin);

            $holidays_rules = $settings->get('holidays_daily');

            $ret = array();

            if ( ! empty( $date ) ) {
                    foreach ($holidays_rules as $rule) {
                            if(
                                    ( $date	=== $rule['from_date'] ||
                                    $date	=== $rule['to_date'] ) &&
                                    $rule['daily']		=== true
                            ) {
                                $rule['from_time']	= date('H:i', strtotime($rule['from_time']));
                                $rule['to_time']	= date('H:i', strtotime($rule['to_time']));

                                $ret[] = $rule;
                            }
                    }
            } else {
                foreach ($holidays_rules as $rule) {
                    $rule['from_time']  = date('H:i', strtotime($rule['from_time']));
                    $rule['to_time']    = date('H:i', strtotime($rule['to_time']));

                    $ret[] = $rule;
                }
            }

            return $this->success_response(array('items' => $this->get_holidays($date)));

        } catch (\Exception $ex) {
            return new \WP_Error( 'salon_rest_cannot_view', $ex->getMessage(), array( 'status' => $ex->getCode() ? $ex->getCode() : 500 ) );
        }
    }

    protected function get_holidays($date = '') {

        $plugin   = SLN_Plugin::getInstance();
	$settings = $plugin->getSettings();
        $formatter = new SLN_Formatter($plugin);

        $attendantObj = false;

        if (in_array(SLN_Plugin::USER_ROLE_STAFF, wp_get_current_user()->roles)) {
            $repo       = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
            $attendants = $repo->getAll();
            foreach ($attendants as $attendant) {
                if ($attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly()) {
                    $attendantObj = $attendant;
                    break;
                }
            }
        }

        if (!$attendantObj) {
            $holidays_rules = apply_filters('sln.get-day-holidays-rules', $settings->get('holidays_daily'));
        } else {
            $holidays_assistants_rules  = array();
            $assistants                 = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT)->getAll();
            foreach ($assistants as $att) {
                $holidays_assistants_rules[$att->getId()] = $att->getMeta('holidays_daily')?:array();
            }
            $holidays_assistants_rules = apply_filters('sln.get-day-holidays-assistants-rules', $holidays_assistants_rules, $assistants);
            $holidays_rules = $holidays_assistants_rules[$attendantObj->getId()];
        }

	$ret = array();

	if ( ! empty( $date ) ) {
		foreach ($holidays_rules as $rule) {
			if(
				( $date	=== $rule['from_date'] ||
				$date	=== $rule['to_date'] ) &&
				$rule['daily']		=== true
			) {
                            $rule['from_time']	= date('H:i', strtotime($rule['from_time']));
                            $rule['to_time']	= date('H:i', strtotime($rule['to_time']));

                            $ret[] = $rule;
                        }
		}
	} else {
            foreach ($holidays_rules as $rule) {
                $rule['from_time']  = date('H:i', strtotime($rule['from_time']));
                $rule['to_time']    = date('H:i', strtotime($rule['to_time']));

                $ret[] = $rule;
            }
	}

        return $ret;
    }

    public function create_holiday_rule( $request )
    {
        try {

            do_action('sln_api_holiday_rules_create_holiday_rule_before', $request);

            do_action('sln_api_holiday_rules_create_holiday_rule_before_check', $request);

            $plugin   = SLN_Plugin::getInstance();
            $settings = $plugin->getSettings();
        $formatter = new SLN_Formatter($plugin);

            $data = array();

            $data['from_date']	= $request->get_param('from_date');
            $data['to_date']	= $request->get_param('to_date');
            $data['from_time']	= $formatter->time($request->get_param('from_time'));
            $data['to_time']	= $formatter->time($request->get_param('to_time'));
            $data['daily']		= true;
        $attendantObj = false;

        if (in_array(SLN_Plugin::USER_ROLE_STAFF, wp_get_current_user()->roles)) {
            $repo       = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
            $attendants = $repo->getAll();
            foreach ($attendants as $attendant) {
                if ($attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly()) {
                    $attendantObj = $attendant;
                    break;
                }
            }
        }

            if($this->validateDate($data['from_date']) && $this->validateDate($data['to_date']) ) {
                    if (!$attendantObj) {
                $applied = apply_filters('sln.add-holiday-rule.add-holidays-daily', false, $data);

                if (!$applied) {
                    $holidays_rules = $settings->get('holidays_daily') ?: array();
                    $holidays_rules[] = $data;

                    $settings->set('holidays_daily', $holidays_rules);
                    $settings->save();
                }

                $bc = $plugin->getBookingCache();
                $bc->refresh($data['from_date'], $data['to_date']);
            } else {
                $applied = apply_filters('sln.add-holiday-rule.add-holidays-daily-assistants', false, $data, $attendantObj->getId());

                if (!$applied) {
                    $holidays_rules   = $attendantObj->getMeta('holidays_daily')?:array();
                    $holidays_rules[] = $data;
                    $attendantObj->setMeta('holidays_daily', $holidays_rules);
                }
                $bc = $plugin->getBookingCache();
                $bc->refresh($data['from_date'],$data['to_date']);
            }
            } else {
                    return new WP_Error( 'salon_rest_cannot_view', __( 'Sorry, error on create ('.$ex->getMessage().').', 'salon-booking-system' ), array( 'status' => 404 ) );
            }

            return $this->success_response(array('items' => $this->get_holidays($data['from_date'])));

        } catch (\Exception $ex) {
            return new \WP_Error( 'salon_rest_cannot_view', $ex->getMessage(), array( 'status' => $ex->getCode() ? $ex->getCode() : 500 ) );
        }
    }

    public function validateDate($date, $format = 'Y-m-d')
    {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) === $date;
    }

    public function delete_holiday_rule( $request )
    {
        try {

            do_action('sln_api_holiday_rules_delete_holiday_rule_before', $request);

            do_action('sln_api_holiday_rules_delete_holiday_rule_before_check', $request);

            $plugin   = SLN_Plugin::getInstance();
            $settings = $plugin->getSettings();
        $formatter = new SLN_Formatter($plugin);

            $attendantObj = false;

            if (in_array(SLN_Plugin::USER_ROLE_STAFF, wp_get_current_user()->roles)) {
                $repo       = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
                $attendants = $repo->getAll();
                foreach ($attendants as $attendant) {
                    if ($attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly()) {
                        $attendantObj = $attendant;
                        break;
                    }
                }
            }

            $data = array();
            $data['from_date']	= $request->get_param('from_date');
            $data['to_date']	= $request->get_param('to_date');
            $data['from_time']	= $formatter->time($request->get_param('from_time'));
            $data['to_time']	= $formatter->time($request->get_param('to_time'));
            $data['daily']		= true;

            $fromDateTime = new SLN_DateTime($data['from_date'] . ' ' . $data['from_time']);
            $toDateTime   = new SLN_DateTime($data['to_date'] . ' ' . $data['to_time']);

            if (!$attendantObj) {
                $applied = apply_filters('sln.remove-holiday-rule.remove-holidays-daily', false, $data);
            } else {
                $applied = apply_filters('sln.remove-holiday-rule.remove-holidays-daily-assistants', false, $data, $attendantObj->getId());
            }

            if (!$applied) {

                    if (!$attendantObj) {
                        $holidays_rules = $settings->get('holidays_daily');
                    } else {
                        $holidays_rules = $attendantObj->getMeta('holidays_daily') ? $attendantObj->getMeta('holidays_daily') : array();
                    }
                    $search_rule=array();

                    foreach ($holidays_rules as $rule) {
                             if((
                                    $data['from_date']	=== $rule['from_date'] &&
                                    $data['to_date']	=== $rule['to_date'] &&
                                    $data['from_time']	=== $formatter->time($rule['from_time']) &&
                                    $data['to_time']	=== $formatter->time($rule['to_time'])
                             )) continue;

                            $ruleFromDateTime = new SLN_DateTime($rule['from_date'] . ' ' . $rule['from_time']);
                            $ruleToDateTime   = new SLN_DateTime($rule['to_date'] . ' ' . $rule['to_time']);

                            if ($fromDateTime >= $ruleFromDateTime && $toDateTime <= $ruleToDateTime) {
                                if ($fromDateTime > $ruleFromDateTime) {
                                    $search_rule[] = array(
                                        'from_date' => $ruleFromDateTime->format('Y-m-d'),
                                        'to_date'   => $fromDateTime->format('Y-m-d'),
                                        'from_time' => $formatter->time($ruleFromDateTime->format('H:i')),
                                        'to_time'   => $formatter->time($fromDateTime->format('H:i')),
                                        'daily'     => true,
                                    );
                                }
                                if ($toDateTime < $ruleToDateTime) {
                                    $search_rule[] = array(
                                        'from_date' => $toDateTime->format('Y-m-d'),
                                        'to_date'   => $ruleToDateTime->format('Y-m-d'),
                                        'from_time' => $formatter->time($toDateTime->format('H:i')),
                                        'to_time'   => $formatter->time($ruleToDateTime->format('H:i')),
                                        'daily'     => true,
                                    );
                                }
                            } else {
                                $search_rule[] = $rule;
                            }
                    }

                    if (!$attendantObj) {
                        $settings->set('holidays_daily',$search_rule);
                        $settings->save();

                        $bc = $plugin->getBookingCache();
                        $bc->refresh($data['from_date'],$data['to_date']);
                    } else {
                        $attendantObj->setMeta('holidays_daily', $search_rule);

                        $bc = $plugin->getBookingCache();
                        $bc->refresh($data['from_date'],$data['to_date']);
                    }
            }

            return $this->success_response(array('items' => $this->get_holidays($data['from_date'])));

        } catch (\Exception $ex) {
            return new \WP_Error( 'salon_rest_cannot_view', $ex->getMessage(), array( 'status' => $ex->getCode() ? $ex->getCode() : 500 ) );
        }
    }

    public function get_item_schema()
    {
        $schema = array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'holiday rule',
            'type'       => 'object',
            'properties' => array(
                'from_date' => array(
                    'description' => __( 'From date.', 'salon-booking-system' ),
                    'type'        => 'string',
               	    'format'      => 'YYYY-MM-DD',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'required'          => true,
			'validate_callback' => array($this, 'rest_validate_request_arg'),
                    ),
                ),
		'to_date' => array(
                    'description' => __( 'To date.', 'salon-booking-system' ),
                    'type'        => 'string',
               	    'format'      => 'YYYY-MM-DD',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'required'          => true,
			'validate_callback' => array($this, 'rest_validate_request_arg'),
                    ),
                ),
		'from_time' => array(
                    'description' => __( 'From time.', 'salon-booking-system' ),
                    'type'        => 'string',
               	    'format'      => 'HH:ii',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'required'          => true,
			'validate_callback' => array($this, 'rest_validate_request_arg'),
                    ),
                ),
		'to_time' => array(
                    'description' => __( 'To time.', 'salon-booking-system' ),
                    'type'        => 'string',
               	    'format'      => 'HH:ii',
                    'context'     => array( 'view', 'edit' ),
                    'arg_options' => array(
                        'required'          => true,
			'validate_callback' => array($this, 'rest_validate_request_arg'),
                    ),
                ),
		'daily' => array(
                    'description' => __( 'Daily.', 'salon-booking-system' ),
                    'type'        => 'boolean',
               	    'context'     => array( 'view' ),
                ),
            ),
        );

        return apply_filters('sln_api_holiday_rules_get_item_schema', $schema);
    }




}