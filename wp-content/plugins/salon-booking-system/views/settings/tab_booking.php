<?php
$plugin = SLN_Plugin::getInstance();
include $this->plugin->getViewFile('admin/utilities/settings_inpage_navbar');
sum(
	// link anchor, link text
	array('#sln-pause_booking_service', __('Pause booking service', 'salon-booking-system')),
	array('#sln-availability_mode', __('Availability mode', 'salon-booking-system')),
	array('#sln-customers_per_session', __('Customers per session', 'salon-booking-system')),
	array('#sln-session_average_duration', __('Session average duration', 'salon-booking-system')),
	array('#sln-booking_steps_order', __('Booking steps order', 'salon-booking-system')),
	array('#sln-offset_between_reservations', __('Offset between reservations', 'salon-booking-system')),
	array('#sln-booking_time_range', __('Booking time range', 'salon-booking-system')),
	array('#sln-online_booking_available_days', __('On-line booking available days', 'salon-booking-system')),
	array('#sln-holidays_days', __('Holidays days', 'salon-booking-system')),
	array('#sln-booking_manual_confirmation', __('Booking manual confirmation', 'salon-booking-system')),
	array('#sln-user_booking_cancellation', __('User booking cancellation', 'salon-booking-system')),
	array('#sln-booking_rescheduling', __('Booking rescheduling', 'salon-booking-system'))
);
?>
<?php
echo $plugin->loadView('settings/_tab_booking_status');
echo $plugin->loadView('settings/_tab_booking_availability');
?>
    <div class="row">
        <div id="sln-customers_per_session" class="col-xs-12 col-sm-6 col-md-6">
            <div class="sln-box sln-box--main sln-box--main--small">
                <h2 class="sln-box-title"><?php _e('Customers per session', 'salon-booking-system');?> <span
                        class="block"><?php _e(
	'How many people you can attend during a single time/session?',
	'salon-booking-system'
)?></span></h2>
                <div class="row">
                    <div class="col-xs-12 form-group sln-select  sln-select--info-label">
                        <div class="row">
                            <div class="col-xs-4">
                                <?php echo SLN_Form::fieldNumeric(
	"salon_settings[parallels_hour]",
	$this->getOpt('parallels_hour'),
	array('min' => 1, 'max' => 1000)
) ?>
                            </div>
                            <div class="col-xs-8 sln-label--big"><label for="salon_settings_sms_remind_interval">Customers
                                    per session</label></div>
                            <div class="col-xs-12">
                                <p class="help-block sln-input-help"><?php _e(
	'Set these options carefully because it will affect the number of bookings you can accept for the same <strong>time/session</strong>.',
	'salon-booking-system'
)?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6">
            <div id="sln-session_average_duration" class="sln-box sln-box--main sln-box--main--small">
                <h2 class="sln-box-title"><?php _e('Session average duration', 'salon-booking-system');?> <span
                        class="block"><?php _e(
	'This option will change the minimum hour fraction of the Time picker.',
	'salon-booking-system'
)?></span></h2>
                <div class="row">
                    <div class="col-xs-12 form-group sln-select  sln-select--info-label">
                        <div class="row">
                            <div class="col-xs-4">
                                <?php $field = "salon_settings[interval]";?>
                                <?php echo SLN_Form::fieldSelect(
	$field,
	SLN_Enum_Interval::toArray(),
	$this->getOpt('interval') ? $this->getOpt('interval') : 15
) ?>
                            </div>
                            <div class="col-xs-8 sln-label--big"><label
                                    for="<?php echo SLN_Form::makeID($field) ?>"><?php _e(
	'Minutes per session',
	'salon-booking-system'
);?></label></div>
                            <div class="col-xs-12">
                                <p class="help-block sln-input-help"><?php _e(
	'We suggest you to set this option accordingly with the duration of your shortest service.',
	'salon-booking-system'
)?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <?php
echo $plugin->loadView('settings/_tab_booking_form_steps_alt_order', array('helper' => $this));
echo $plugin->loadView('settings/_tab_booking_offset', array('helper' => $this));
echo $plugin->loadView('settings/_tab_booking_timing');
echo $plugin->loadView('settings/_tab_booking_rules',
	array(
		'availabilities' => $plugin->getSettings()->get('availabilities'),
		'base' => 'salon_settings[availabilities]',
	)
);
echo $plugin->loadView(
	'settings/_tab_booking_holiday_rules',
	array(
		'holidays' => $plugin->getSettings()->get('holidays'),
		'base' => 'salon_settings[holidays]',
	)
);
echo $plugin->loadView('settings/_tab_booking_confirmation', array('helper' => $this));
echo $plugin->loadView('settings/_tab_booking_cancellation', array('helper' => $this));
echo $plugin->loadView('settings/_tab_booking_rescheduling', array('helper' => $this));
?>


