<?php
/**
 * @var $plugin SLN_Plugin
 * @var $helper SLN_Admin_Settings
 */
?>
<div id="sln-sms_services" class="sln-box sln-box--main sln-box--haspanel">
<h2 class="sln-box-title sln-box__paneltitle"><?php _e('SMS services', 'salon-booking-system')?></h2>
<div class="collapse sln-box__panelcollapse">
    <div class="sln-box--sub row">
    <div class="col-xs-12">
        <h2 class="sln-box-title"><?php _e('SMS service provider', 'salon-booking-system')?></h2>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-md-push-8 sln-box-maininfo align-top">
                <p class="sln-box-info"><?php _e(
	'To use all the SMS features you need an active account with Plivo o Twilio providers. <br /><br />Please read carefully their documentation about how to properly set the options.',
	'salon-booking-system'
)?></p>
            </div>
            <div class="col-xs-12 col-sm-8- col-md-8 col-md-pull-4">
                <div class="row">
                    <div class="col-xs-12 col-sm-6 sln-select">
                        <?php $field = "salon_settings[sms_provider]";?>
                        <label for="salon_settings_sms_provider"><?php _e(
	'Select your service provider',
	'salon-booking-system'
)?></label>
                        <?php echo SLN_Form::fieldSelect(
	$field,
	SLN_Enum_SmsProvider::toArray(),
	$plugin->getSettings()->get('sms_provider'),
	array(),
	true
) ?>
                    </div>
                </div>
		<?php foreach (SLN_Enum_SmsProvider::toArray() as $k => $v): ?>
		    <div class="sms-provider-data" style="display: none;" id="sms-provider-<?php echo $k ?>">
			<?php
echo SLN_Enum_SmsProvider::getService($k, $plugin)->renderSettingsFields(array('helper' => $helper));
?>
		    </div>
		<?php endforeach;?>
		<div class="sms-provider-data" id="sms-provider-default">
		    <div class="row">
			<div class="col-xs-12 col-sm-6 sln-input--simple">
			    <?php $helper->row_input_text('sms_account', __('Account ID', 'salon-booking-system'));?>
			</div>
			<div class="col-xs-12 col-sm-6 sln-input--simple">
			    <?php $helper->row_input_text('sms_password', __('Auth Token', 'salon-booking-system'));?>
			</div>
		    </div>
		    <div class="row">
			<div class="col-xs-12 col-sm-6 form-group sln-input--simple">
			    <?php $helper->row_input_text('sms_prefix', __('Country code', 'salon-booking-system'));?>
			</div>
			<div class="col-xs-12 col-sm-6 form-group sln-input--simple">
			    <?php $helper->row_input_text('sms_from', __('Sender\'s number', 'salon-booking-system'));?>
			</div>
		    </div>
		    <div class="row">
			<div class="col-xs-12 col-sm-6 form-group sln-checkbox">
			    <?php $helper->row_input_checkbox(
	'sms_trunk_prefix',
	__('Trunk trailing 0 prefix', 'salon-booking-system')
);?>
			</div>
		    </div>
		</div>
            </div>

</div>
<div class="sln-box--sub row">
    <div class="col-xs-12">
        <h2 class="sln-box-title"><?php _e('SMS Test console', 'salon-booking-system')?><span><?php _e(
	'fill the fields and update settings',
	'salon-booking-system'
)?></span></h2>
    </div>
    <div class="col-xs-12 col-sm-4 form-group sln-input--simple">
        <?php $helper->row_input_text('sms_test_number', __('Number', 'salon-booking-system'));?>
    </div>
    <div class="col-xs-12 col-sm-4 form-group sln-input--simple">
        <?php $helper->row_input_text('sms_test_message', __('Message', 'salon-booking-system'));?>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 sln-box-maininfo align-top">
        <p class="sln-box-info"><?php _e(
	'Use this console just to test your SMS services. Fill the destination number without the country code, write a text message and click "Update settings" to send an SMS.',
	'salon-booking-system'
);?></p>
    </div>
</div>
<div class="sln-box--sub row">
    <div class="col-xs-12">
        <h2 class="sln-box-title"><?php _e('SMS Notifications service', 'salon-booking-system')?></h2>
    </div>
    <div class="col-xs-12">
        <div class="row sln-input__group">
            <div class="col-xs-12 col-sm-6 col-md-6">
                <div class="sln-checkbox">
                <?php $helper->row_input_checkbox(
	'sms_new',
	__('Send SMS notification on new bookings', 'salon-booking-system')
);?>
                </div>
                <div class="sln-box-maininfo">
                    <p class="sln-box-info">
                        <?php _e('SMS will be sent to your customer and a staff member', 'salon-booking-system');?>
                    </p>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6">
                <div class="sln-checkbox">
                    <?php $helper->row_input_checkbox(
	'sms_modified',
	__('Send SMS notification on modified bookings', 'salon-booking-system')
);?>
                </div>
            </div>

        </div>
        <div class="row sln-input__group">
            <div class="col-xs-12 col-sm-6 col-md-4 form-group sln-input--simple">
                <?php $helper->row_input_text(
	'sms_new_number',
	__('Staff member notification number', 'salon-booking-system')
);?>
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="row sln-input__group">
            <div class="col-xs-12 col-sm-6 form-group sln-checkbox">
                <?php $helper->row_input_checkbox(
	'sms_remind',
	__('Remind the appointment to the client with an SMS', 'salon-booking-system')
);?>
            </div>
            <div class="col-xs-12 col-sm-6 form-group sln-select  sln-select--info-label">
                <label for="salon_settings_sms_remind_interval"><?php __(
	'SMS Timing',
	'salon-booking-system'
)?></label>
                <div class="row">
                    <div class="col-xs-5 col-sm-4">
                        <?php $field = "salon_settings[sms_remind_interval]";?>
                        <?php echo SLN_Form::fieldSelect(
	$field,
	SLN_Func::getIntervalItemsShort(),
	$plugin->getSettings()->get('sms_remind_interval'),
	array(),
	true
) ?>
                    </div>
                    <div class="col-xs-7 col-sm-8 sln-label--big">
                        <label for="salon_settings_sms_remind_interval"><?php _e('Before the appointment', 'salon-booking-system')?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="row sln-input__group">
            <div class="col-xs-12 col-sm-6">
                <div class="sln-checkbox">
                <?php $helper->row_input_checkbox(
	'sms_new_attendant',
	__('Send an SMS to selected attendant on new bookings', 'salon-booking-system')
);?>
                </div>
                <div class="sln-box-maininfo">
                    <p class="sln-box-info">
                        <?php _e('Remember to set the mobile number of your staff members', 'salon-booking-system');?>
                    </p>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="sln-checkbox">
                <?php $helper->row_input_checkbox(
	'sms_modified_attendant',
	__('Send an SMS to selected attendant on modified bookings', 'salon-booking-system')
);?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="row sln-input__group">
            <div class="col-xs-12 col-sm-6 enabled-whatsapp-checkbox
                <?php echo $plugin->getSettings()->get('sms_provider') === 'twilio' ? '' : 'hide' ?>
                "
            >
            <div class="sln-checkbox">
                <?php $helper->row_input_checkbox(
	'whatsapp_enabled',
	__('Enable Whatsapp notification', 'salon-booking-system')
);?>
                </div>
                <div class="sln-box-maininfo">
                    <p class="sln-box-info">
                       <?php _e('Will be used Whatsapp messenger for deliver your notifications', 'salon-booking-system');?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-12 col-sm-8 form-group sln-input--simple sln-input--mt">
                <?php $helper->row_input_textarea('sms_notification_message', __('Customize the SMS notification message on new bookings (max 160 characters)', 'salon-booking-system'), array(
	'textarea' => array(
		'attrs' => array(
			'placeholder' => str_replace("\r\n", " ", SLN_Admin_SettingTabs_GeneralTab::getDefaultSmsNotificationMessage()),
		),
	),
));?>
                <p class="sln-input-help">
                    <?php _e('You can use [NAME], [SALON NAME], [DATE], [TIME], [PRICE], [BOOKING ID]', 'salon-booking-system')?>
                </p>
            </div>
	    </div>
    </div>
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-12 col-sm-8 form-group sln-input--simple sln-input--mt">
                <?php $helper->row_input_textarea('sms_notification_message_modified', __('Customize the SMS notification message on modified bookings (max 160 characters)', 'salon-booking-system'), array(
	'textarea' => array(
		'attrs' => array(
			'placeholder' => str_replace("\r\n", " ", SLN_Admin_SettingTabs_GeneralTab::getDefaultSmsNotificationMessageModified()),
		),
	),
));?>
                <p class="sln-input-help">
                    <?php _e('You can use [NAME], [SALON NAME], [DATE], [TIME], [PRICE], [BOOKING ID]', 'salon-booking-system')?>
                </p>
            </div>
	    </div>
    </div>
    <div class="clearfix"></div>
</div>
<div class="sln-box--sub row">
    <div class="col-xs-12">
        <h2 class="sln-box-title"><?php _e(
	'SMS Verification service <span>Ask users to verify their identity with an SMS verification code</span>',
	'salon-booking-system'
)?></h2>
    </div>
    <div class="col-xs-12 col-sm-6 sln-checkbox">
        <?php $helper->row_input_checkbox('sms_enabled', __('Enable SMS verification', 'salon-booking-system'));?>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 sln-box-maininfo align-top">
        <p class="sln-box-info">
            <?php _e(
	'Avoid spam asking your users to verify their identity with an SMS verification code during the first registration.',
	'salon-booking-system'
)?>
        </p>
    </div>
</div>
</div>
</div>
