<?php
$plugin = SLN_Plugin::getInstance();
include $this->plugin->getViewFile('admin/utilities/settings_inpage_navbar');
sum(
	// link anchor, link text
	array('#sln-salon_information', __('Salon information', 'salon-booking-system')),
	array('#sln-date_time_settings', __('Date and Time settings', 'salon-booking-system')),
	array('#sln-assistant_selection', __('Assistant selection', 'salon-booking-system')),
	array('#sln-sms_services', __('SMS services', 'salon-booking-system')),
	array('#sln-email_new_booking', __('Email notification services', 'salon-booking-system')),
	array('#sln-automatic_follow-up', __('Automatic follow-up', 'salon-booking-system')),
	array('#sln-automatic_feedback_reminder', __('Automatic feedback reminder', 'salon-booking-system')),
	array('#sln-api_services', __('API services', 'salon-booking-system')),
	array('#sln-administration_rules', __('Administration rules', 'salon-booking-system')),
	array('#sln-salon_booking_system_required_pages', __('Salon Booking System required pages', 'salon-booking-system'))
);
?>

<div id="sln-salon_information" class="sln-box sln-box--main sln-box--haspanel sln-box--haspanel--open">
    <h2 class="sln-box-title sln-box__paneltitle sln-box__paneltitle--open"><?php _e('Salon information', 'salon-booking-system');?></h2>
    <div class="collapse in sln-box__panelcollapse">
    <div class="row">
        <div class="col-xs-12 col-sm-4 form-group sln-input--simple">
            <?php
$this->row_input_text(
	'gen_name',
	__('Your salon name', 'salon-booking-system'),
	array(
		'help' => sprintf(
			__('Leaving this field empty will cause the default site name <strong>(%s)</strong> to be used', 'salon-booking-system'),
			get_bloginfo('name')
		),
	)
);
?>
        </div>
        <div class="col-xs-12 col-sm-4 form-group sln-input sln-input--simple">
        <?php
$this->row_input_email(
	'gen_email',
	__('Salon contact e-mail', 'salon-booking-system'),
	array(
		'help' => sprintf(
			__('Leaving this field empty will cause the default site email  <strong>(%s)</strong> to be used', 'salon-booking-system'),
			esc_html(get_bloginfo('admin_email'))
		),
	)
);
?>
        </div>
        <div class="col-xs-12 col-sm-4 form-group sln-input--simple">
            <?php $this->row_input_text('gen_phone', __('Salon telephone number', 'salon-booking-system'));?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-8 form-group sln-input--simple">
            <?php $this->row_input_textarea(
	'gen_address',
	__('Salon address', 'salon-booking-system'),
	array(
		'textarea' => array(
			'attrs' => array(
				'rows' => 5,
				'placeholder' => __('write your address', 'salon-booking-system'),
			),
		),
		'help' => __('Provide the full address of your Salon', 'salon-booking-system'),
	)
);?>
        </div>
        <div class="col-xs-12 col-sm-4 form-group sln-input--simple sln-logo-box">
            <label for="gen_logo"><?php _e('Upload your logo', 'salon-booking-system')?></label>
            <?php if ($this->getOpt('gen_logo')): ?>
                <div id="logo" class="preview-logo">
                    <div class="preview-logo-img">
                        <img src="<?php echo wp_get_attachment_image_url($this->getOpt('gen_logo'), 'sln_gen_logo'); ?>">
                    </div>
                    <button type="button" class="sln-btn sln-btn--light sln-btn--medium sln-btn--icon sln-icon--trash" data-action="delete-logo" data-target-remove="logo"
                            data-target-reset="salon_settings_gen_logo" data-target-show="select_logo"><?php _e('Remove this image', 'salon-booking-system');?></button>
                </div>
            <?php endif?>

            <div id="select_logo" class="select-logo <?php echo $this->getOpt('gen_logo') ? 'hide' : '' ?>" data-action="select-logo" data-target="gen_logo">
                <span class="dashicons dashicons-upload"></span>
            </div>

            <div class="hide">
                <input type="file" name="gen_logo" id="gen_logo" data-action="select-file-logo" accept="image/png">
                <input type="hidden" name="salon_settings[gen_logo]" id="salon_settings_gen_logo" value="<?php echo $this->getOpt('gen_logo'); ?>">
            </div>

            <p class="help-block"><?php _e('Use a transparent png file', 'salon-booking-system')?></p>
        </div>
    </div>
</div>
</div>
<div id="sln-date_time_settings" class="sln-box sln-box--main  sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Date and Time settings', 'salon-booking-system')?></h2>
    <div class="collapse sln-box__panelcollapse">
    <div class="row">
            <div class="col-xs-6 col-sm-6 col-md-4 form-group sln-select ">
                <label for="salon_settings_date_format"><?php _e('Date Format', 'salon-booking-system')?></label>
                <?php $field = "salon_settings[date_format]";?>
                <?php echo SLN_Form::fieldSelect(
	$field,
	SLN_Enum_DateFormat::toArray(),
	$this->getOpt('date_format'),
	array(),
	true
) ?>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-4 form-group sln-select ">
                <label for="salon_settings_time_format"><?php _e('Time Format', 'salon-booking-system')?></label>
                <?php $field = "salon_settings[time_format]";?>
                    <?php echo SLN_Form::fieldSelect(
	$field,
	SLN_Enum_TimeFormat::toArray(),
	$this->getOpt('time_format'),
	array(),
	true
) ?>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 sln-box-maininfo align-top">
                <p class="sln-box-info"><?php _e('Select your favourite date and time format. Do you need another format? Send an email to support@wpchef.it', 'salon-booking-system')?></p>
            </div>
            </div>
    <div class="row">
        <div class="col-xs-6 col-sm-6 col-md-4 form-group sln-select ">
            <label for="salon_settings_week_start"><?php _e('Start week on', 'salon-booking-system')?></label>
            <?php $field = "salon_settings[week_start]";?>
            <?php echo SLN_Form::fieldSelect(
	$field,
	SLN_Enum_DaysOfWeek::toArray(),
	$this->getOpt('week_start'),
	array(),
	true
) ?>
        </div>
        <div class="col-xs-6 col-sm-6 col-md-4 form-group sln-select ">
            <label for="salon_settings_calendar_view"><?php _e('Default Calendar View', 'salon-booking-system')?></label>
            <?php $field = "salon_settings[calendar_view]";?>
            <?php echo SLN_Form::fieldSelect(
	$field,
	array('month' => 'Month', 'week' => 'Week', 'day' => 'Day'),
	$this->getOpt('calendar_view'),
	array(),
	true
) ?>
        </div>
        <div class="col-xs-6 col-sm-6 col-md-4 form-group sln-checkbox sln-display-slots-customers-timezone">
            <?php $attrs = !defined("SLN_VERSION_PAY") ? array('disabled' => 'disabled') : array() ?>
            <div class="<?php echo !defined("SLN_VERSION_PAY") ? 'sln-disabled' : '' ?>">
                <?php $this->row_input_checkbox('display_slots_customer_timezone', __("Display slots using customer's time-zone", 'salon-booking-system'), array('attrs' => $attrs));?>
            </div>
            <?php if (!defined("SLN_VERSION_PAY")): ?>
                <div class="sln-disabled-free-version">
                    <?php _e('Available on PRO version', 'salon-booking-system') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
</div>
<div id="sln-assistant_selection" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Assistant selection', 'salon-booking-system')?></h2>
   <div class="collapse sln-box__panelcollapse">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="sln-switch">
                <?php $this->row_input_checkbox('attendant_enabled', __('Enable assistant selection', 'salon-booking-system'));?>
            </div>
            <div class="sln-box-maininfo">
                <p class="sln-box-info">
                <?php _e('Let your customers choose their favourite staff member.', 'salon-booking-system')?>
                </p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 only-from-backend-attendant-enable-checkbox <?php echo $this->getOpt('attendant_enabled') ? '' : 'hide' ?>">
            <div class="sln-checkbox">
                <?php $this->row_input_checkbox('only_from_backend_attendant_enabled', __('Only from back-end', 'salon-booking-system'));?>
            </div>
            <div class="sln-box-maininfo">
                <p class="sln-box-info">
                    <?php _e('If enabled the assistant selection step will be hidden on front-end.', 'salon-booking-system')?>
                    <?php _e('Assistants will be assigned automatically by the system.', 'salon-booking-system')?>
                </p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 assistant-selections-options <?php echo $this->getOpt('attendant_enabled') ? '' : 'hide' ?>">
            <div class="sln-checkbox">
                <?php $this->row_input_checkbox('hide_invalid_attendants_enabled', __('Hide unavailable assistants', 'salon-booking-system')); ?>
            </div>
        </div>
        <!-- .row // END -->
    </div>
    <div class="row assistant-selections-options <?php echo $this->getOpt('attendant_enabled') ? '' : 'hide' ?>">
        <div class="col-xs-12 col-sm-6 col-md-4 from-group">
            <div class="sln-checkbox">
                <?php $this->row_input_checkbox('skip_attendants_enabled', __('Skip assistant selection step', 'salon-booking-system')); ?>
            </div>
            <div class="sln-box-maininfo">
                <p class="sln-box-info">
                    <?php _e('Skip assistant selection step, if only one is available', 'salon-booking-system'); ?>
                </p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 form-group">
            <div class="sln-checkbox">
                <?php $this->row_input_checkbox('choose_attendant_for_me_disabled', __('Disable "Choose an assistant for me"', 'salon-booking-system'));?>
            </div>
            <div class="sln-box-maininfo">
                <p class="sln-box-info">
                    <?php _e('When checked the option "Choose an assistant for me" will be removed on front-end Assistants selection step.', 'salon-booking-system')?>
                </p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 form-group">
            <div class="sln-checkbox">
                <?php $this->row_input_checkbox('m_attendant_enabled', __('Enable multiple assistants selection', 'salon-booking-system'));?>
            </div>
            <div class="sln-box-maininfo">
                <p class="sln-box-info">
                    <?php _e('Users can select more than one assistants for their booked services. Please set with care the "execution order" inside your services section.', 'salon-booking-system')?>
                </p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 form-group">
            <div class="sln-checkbox">
                <?php $this->row_input_checkbox('attendant_email', __('Enable assistant email on new bookings', 'salon-booking-system'));?>
            </div>
            <div class="sln-box-maininfo">
                <p class="sln-box-info">
                    <?php _e('Assistants will receive an e-mail when selected for a new booking.', 'salon-booking-system')?>
                </p>
            </div>
        </div>
        <!-- .row // END -->
    </div>

    <div class="row sln-box__footer sln-box__actions">
        <div class="col-xs-12 col-sm-6 form-group">
            <a href="<?php echo get_admin_url() . 'edit.php?post_type=sln_attendant'; ?> "
               class="sln-btn sln-btn--main sln-btn--big sln-btn--icon sln-icon--assistants"><?php _e('Manage staff', 'salon-booking-system')?></a>
            <div class="sln-box-maininfo align-top">
                <p class="sln-box-info">
                    <?php _e('If you need to add or manage your staff members.', 'salon-booking-system', 'salon-booking-system')?>
                </p>
            </div>
        </div>
    </div>
    </div>
</div>
<?php echo SLN_Plugin::getInstance()->loadView('settings/_tab_general_sms', array('helper' => $this)); ?>
<div id="sln-email_new_booking" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('New booking email notification to customer', 'salon-booking-system')?></h2>
    <div class="collapse sln-box__panelcollapse">
    <div class="row">
        <div class="col-xs-12">
                <div class="row sln-input--mt">
                <div class="col-xs-12 col-sm-8 col-md-6 sln-input--simple">
                    <?php $this->row_input_text('email_nb_subject', __('Email Subject', 'salon-booking-system'));?>
                    <p class="sln-input-help"><?php _e('You can use [DATE], [TIME], [SALON NAME]', 'salon-booking-system')?></p>
                    <div class="sln-checkbox">
                        <?php $this->row_input_checkbox('disable_new_user_welcome_email', __('Disable new user welcome email', 'salon-booking-system'));?>
                    </div>
                    <div class="sln-box-maininfo">
                        <p class="sln-box-info"></p>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-8 col-md-6 sln-input--simple">
                    <?php $this->row_input_textarea('new_booking_message', __('Customize the booking notification message', 'salon-booking-system'));?>
                    <p class="sln-input-help"><?php _e('You can use [DATE], [TIME], [NAME], [SALON NAME]', 'salon-booking-system')?></p>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<div id="sln-email_services" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Email notifications service', 'salon-booking-system')?></h2>
    <div class="collapse sln-box__panelcollapse">
        <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12 col-sm-8 col-md-6 form-group sln-checkbox">
                    <?php $this->row_input_checkbox('email_remind', __('Remind the appointment to the client with an Email', 'salon-booking-system'));?>
                </div>
                <div class="col-xs-12 col-sm-8 col-md-6 form-group sln-select  sln-select--info-label">
                    <label for="salon_settings_email_remind_interval"><?php _e('Email Timing', 'salon-booking-system')?></label>
                    <div class="row">
                        <div class="col-xs-6 col-sm-6">
                            <?php $field = "salon_settings[email_remind_interval]";?>
                            <?php echo SLN_Form::fieldSelect(
	$field,
	SLN_Func::getIntervalItemsShort(),
	$this->getOpt('email_remind_interval'),
	array(),
	true
) ?>
                        </div>
                        <div class="col-xs-6 col-sm-6 sln-label--big">

                            <label for="salon_settings_email_remind_interval"><?php _e('Before the appointment', 'salon-booking-system')?></label></div>
                    </div>
                </div>
            </div>
            <div class="row sln-input--mt">
                <div class="col-xs-12 col-sm-8 col-md-6 sln-input--simple">
                    <?php $this->row_input_text('email_subject', __('Email Subject', 'salon-booking-system'));?>
                    <p class="sln-input-help"><?php _e('You can use [DATE], [TIME], [SALON NAME]', 'salon-booking-system')?></p>
                </div>
                <div class="col-xs-12 col-sm-8 col-md-6 sln-input--simple">
                    <?php $this->row_input_textarea('booking_update_message', __('Customize the booking reminder message', 'salon-booking-system'));?>
                    <p class="sln-input-help"><?php _e('You can use [DATE], [TIME], [NAME], [SALON NAME]', 'salon-booking-system')?></p>
                </div>
            </div>


        </div>
    </div>
</div>
</div>

<div id="sln-automatic_follow-up" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Automatic follow-up', 'salon-booking-system')?></h2>
    <div class="collapse sln-box__panelcollapse">
        <div class="row">
        <div class="col-xs-12">
            <h3 class="sln-box-title--sec "><?php _e('Enable reservation follow-up', 'salon-booking-system')?></h3>
        </div>
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12 col-sm-3 form-group sln-checkbox">
                    <?php $this->row_input_checkbox('follow_up_email', __('by Email', 'salon-booking-system'));?>
                </div>
                <div class="col-xs-12 col-sm-3 form-group sln-checkbox">
                    <?php $this->row_input_checkbox('follow_up_sms', __('by SMS', 'salon-booking-system'));?>
                </div>
                <div class="col-xs-12 col-sm-8 col-md-6 form-group sln-select  sln-select--info-label">
                    <label for="salon_settings_follow_up_interval"><?php _e('Timing', 'salon-booking-system')?></label>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 col-md-8 col-lg-6">
                            <?php SLN_Form::fieldSelect(
	'salon_settings[follow_up_interval]',
	array(
		'+1 days' => '1 ' . __('day', 'salon-booking-system'),
		'+2 days' => '2 ' . __('days', 'salon-booking-system'),
		'+3 days' => '3 ' . __('days', 'salon-booking-system'),
		'+4 days' => '4 ' . __('days', 'salon-booking-system'),
		'+5 days' => '5 ' . __('days', 'salon-booking-system'),
		'+1 weeks' => '1 ' . __('week', 'salon-booking-system'),
		'+2 weeks' => '2 ' . __('weeks', 'salon-booking-system'),
		'+3 weeks' => '3 ' . __('weeks', 'salon-booking-system'),
		'+1 months' => '1 ' . __('month', 'salon-booking-system'),
		'+2 months' => '2' . __('months', 'salon-booking-system'),
		'+3 months' => '3' . __('months', 'salon-booking-system'),
		'+10 months' => '10' . __('months', 'salon-booking-system'),
		'+24 months' => '24' . __('months', 'salon-booking-system'),
		'custom' => __('Customer habit', 'salon-booking-system'),
	),
	$this->getOpt('follow_up_interval'),
	array(),
	true
)?>
                            <div class="row">
                                <div class="col-xs-offset-1 col-md-offset-1">
                                    <p id="salon_settings_follow_up_interval_custom_hint" class="help-block"><?php _e('We\'ll send a message two days before the <strong>next estimated booking</strong>', 'salon-booking-system')?></p>
                                </div>
                            </div>

                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-6 sln-label--big">
                            <label id="salon_settings_follow_up_interval_hint" for="salon_settings_follow_up_interval"><?php _e('After the last appointment', 'salon-booking-system')?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-8 form-group sln-input--simple sln-input--mt">
                    <?php $this->row_input_textarea(
	'follow_up_message',
	__('Custom message (max 500 characters)', 'salon-booking-system'),
	array(
		'textarea' => array(
			'attrs' => array(
				'style' => 'height: 140px;',
				'maxlength' => 500,
				'placeholder' => __('write message', 'salon-booking-system'),
			),
		),
	)
);?><p class="sln-input-help"><?php _e('You can use this dynamic tags: [NAME], [SALON NAME]', 'salon-booking-system')?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div id="sln-automatic_feedback_reminder" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Automatic feedback reminder', 'salon-booking-system')?>
                <span class="block"><?php _e('If enabled an email/sms will be sent automatically to the customer one day after the last visit to the salon.', 'salon-booking-system')?></span></h2>
    <div class="collapse sln-box__panelcollapse">
        <div class="row">
        <div class="col-xs-12">
            <h3 class="sln-box-title--sec ">
                <?php _e('Enable feedback submission request', 'salon-booking-system')?>
             </h3>
        </div>
        <div class="col-xs-12">

            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="row">
                        <div class="col-xs-6 sln-checkbox">
                            <?php $this->row_input_checkbox('feedback_email', __('by Email', 'salon-booking-system'));?>
                        </div>
                        <div class="col-xs-6 sln-checkbox">
                            <?php $this->row_input_checkbox('feedback_sms', __('by SMS', 'salon-booking-system'));?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="sln-input--simple">
                        <?php $this->row_input_text('custom_feedback_url', __('Feedback submission URL', 'salon-booking-system'));?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="sln-input--simple sln-input--mt">
                        <?php $this->row_input_text('feedback_email_subject', __('Subject for feedback mail', 'salon-booking-system'));?>
                    </div>

                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="sln-input--simple sln-input--mt">
                    <?php $this->row_input_textarea(
	'feedback_message',
	__('Custom message (max 500 characters)', 'salon-booking-system'),
	array(
		'textarea' => array(
			'attrs' => array(
				'style' => 'height: 140px;',
				'maxlength' => 500,
				'placeholder' => __('write message', 'salon-booking-system'),
			),
		),
	)
);?>
<p class="sln-input-help"><?php _e('You can use this dynamic tags: [NAME], [SALON NAME]', 'salon-booking-system')?></p>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div id="sln-api_services" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('API services', 'salon-booking-system')?></h2>
    <div class="collapse sln-box__panelcollapse">
    <?php echo SLN_Plugin::getInstance()->loadView('settings/_tab_general_onesignal', array('helper' => $this)); ?>
    <div class="sln-box--sub row">
	<div class="col-xs-12">
	    <h2 class="sln-box-title"><?php _e('Address auto-fill', 'salon-booking-system')?></h2>
	</div>
	<div class="col-xs-12">
	    <div class="row">
		<div class="col-xs-12 col-sm-4 form-group sln-input--simple">
		    <input type="hidden" id="salon_settings_google_maps_api_key_valid" name="salon_settings[google_maps_api_key_valid]"
		    value="<?php echo $this->getOpt('google_maps_api_key_valid', isset($settings['default']) ? $settings['default'] : null) ?>">
		    <?php $this->row_input_text(
	'google_maps_api_key',
	__('Google Places API Key', 'salon-booking-system')
);?>
		</div>
	    </div>
	</div>
    </div>
    <div class="sln-box--sub row">
	<div class="col-xs-12">
	    <h2 class="sln-box-title"><?php _e('Zapier', 'salon-booking-system')?></h2>
	</div>
	<div class="col-xs-12 col-sm-12">
	    <div class="row">
		<div class="col-xs-12 col-sm-6 sln-input--simple">
		    <?php $this->row_input_text(
	'zapier_site_url',
	__('Site Url', 'salon-booking-system'),
	array(
		'attrs' => array('readonly' => "true"),
		'default' => \SLB_Zapier\Webhook::get_url(),
	)
);?>
		</div>
		<div class="col-xs-12 col-sm-6 sln-input--simple">
		    <?php $this->row_input_text(
	'zapier_api_key',
	__('API Key', 'salon-booking-system'),
	array(
		'default' => \SLB_Zapier\Webhook::get_api_key(),
	)
);?>
		</div>
	    </div>
	</div>
    </div>
</div>
</div>
<?php do_action('sln.template.settings.general.before_date_time', $this)?>
<div id="sln-administration_rules" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e('Administration rules', 'salon-booking-system')?></h2>
    <div class="collapse sln-box__panelcollapse">
        <div class="row">
    <div class="col-xs-12 col-sm-6 sln-checkbox">
        <?php $this->row_input_checkbox('editors_manage_cap', __('Enable Editors as administrator', 'salon-booking-system'));?>
        <p><?php _e('This allows Wordpress users with Editor role to manage the Salon Booking section.', 'salon-booking-system')?></p>
    </div>
    <div class="col-xs-12 col-sm-6 sln-checkbox">
        <?php $this->row_input_checkbox('salon_staff_manage_cap_export_csv', __('Allow Assistants to export bookings to CSV', 'salon-booking-system'));?>
        <p><?php _e('This allows Wordpress users with Salon Staff role to export the bookings to CSV.', 'salon-booking-system')?></p>
    </div>
</div>
</div>
</div>
<?php echo $plugin->loadView('settings/_tab_general_pages', array('helper' => $this)); ?>
<?php echo $plugin->loadView( 'settings/_tab_general_reset', array( 'helper' => $this ) ); ?>
