<?php
class SLN_Admin_SettingTabs_GeneralTab extends SLN_Admin_SettingTabs_AbstractTab
{
	protected $fields = array(
		'gen_name',
		'gen_email',
		'gen_phone',
		'gen_address',
		'gen_logo',
		'editors_manage_cap',
		'attendant_enabled',
		'only_from_backend_attendant_enabled',
		'm_attendant_enabled',
		'hide_invalid_attendants_enabled',
		'skip_attendants_enabled',
		'attendant_email',
		'choose_attendant_for_me_disabled',
		'sms_enabled',
		'sms_account',
		'sms_password',
		'sms_prefix',
		'sms_provider',
		'sms_from',
		'sms_new',
		'sms_new_number',
		'sms_modified',
		'sms_new_attendant',
		'sms_modified_attendant',
		'whatsapp_enabled',
		'sms_notification_message',
		'sms_notification_message_modified',
		'sms_remind',
		'sms_remind_interval',
		'sms_trunk_prefix',
		'email_remind',
		'email_remind_interval',
		'email_subject',
		'booking_update_message',
		'email_nb_subject',
		'new_booking_message',
		'disable_new_user_welcome_email',
		'follow_up_email',
		'follow_up_sms',
		'follow_up_interval',
		'follow_up_message',
		'feedback_email',
		'feedback_sms',
		'custom_feedback_url',
		'feedback_email_subject',
		'feedback_message',
		'soc_facebook',
		'soc_twitter',
		'soc_google',
		'onesignal_app_id',
		'onesignal_new',
		'onesignal_notification_message',
		'google_maps_api_key',
		'google_maps_api_key_valid',
		'zapier_api_key',
		'date_format',
		'time_format',
		'week_start',
		'calendar_view',
		'sms_test_number',
		'sms_test_message',
		'salon_staff_manage_cap_export_csv',
		'thankyou',
		'pay',
		'bookingmyaccount',
	);

	protected function validate() {

		if (!empty($this->submitted['gen_email']) && !filter_var($this->submitted['gen_email'], FILTER_VALIDATE_EMAIL)) {
			$this->showAlert('error', __('Invalid Email in Salon contact e-mail field', 'salon-booking-system'));
			return;
		}


		if (empty($this->submitted['gen_logo']) && $this->getOpt('gen_logo')) {
			wp_delete_attachment($this->getOpt('gen_logo'), true);
		}

		if (isset($_FILES['gen_logo']) && !empty($_FILES['gen_logo']['size'])) {
			$_FILES['gen_logo']['name'] = 'gen_logo.png';

			$imageSize = 'sln_gen_logo';
			if (!has_image_size($imageSize)) {
				add_image_size($imageSize, 160, 70);
			}
			$attId = media_handle_upload('gen_logo', 0);

			if (!is_wp_error($attId)) {
				$this->submitted['gen_logo'] = $attId;
			}
		}

		$this->submitted['sms_notification_message'] = !empty($this->submitted['sms_notification_message']) ?
		$this->submitted['sms_notification_message']
		:
		self::getDefaultSmsNotificationMessage();

		$this->submitted['sms_notification_message_modified'] = !empty($this->submitted['sms_notification_message_modified']) ?
		$this->submitted['sms_notification_message_modified']
		:
		self::getDefaultSmsNotificationMessageModified();

		$this->submitted['email_subject'] = !empty($this->submitted['email_subject']) ?
		$this->submitted['email_subject'] :
		'Your booking reminder for [DATE] at [TIME] at [SALON NAME]';
		$this->submitted['booking_update_message'] = !empty($this->submitted['booking_update_message']) ?
		$this->submitted['booking_update_message'] :
		'Hi [NAME],\r\ntake note of the details of your reservation at [SALON NAME]';
		$this->submitted['email_nb_subject'] = !empty($this->submitted['email_nb_subject']) ?
		$this->submitted['email_nb_subject'] :
		'Your booking for [DATE] at [TIME] at [SALON NAME]';
		$this->submitted['new_booking_message'] = !empty($this->submitted['new_booking_message']) ?
		$this->submitted['new_booking_message'] :
		'Hi [NAME],\r\ntake note of the details of your reservation at [SALON NAME]';
		$this->submitted['follow_up_message'] = !empty($this->submitted['follow_up_message']) ?
		$this->submitted['follow_up_message'] :
		'Hi [NAME],\r\nIt\'s been a while since your last visit, would you like to book a new appointment with us?\r\n\r\nWe look forward to seeing you again.';
		$this->submitted['follow_up_message'] = substr($this->submitted['follow_up_message'], 0, 500);
		$this->submitted['feedback_message'] = !empty($this->submitted['feedback_message']) ?
		$this->submitted['feedback_message'] :
		'Hi [NAME],\r\nwould you like to leave a review on your last appointment at [SALON NAME]?\r\nYour feedback we\'ll help us improving our services and providing you a better experience in the future.';
		$this->submitted['feedback_message'] = substr($this->submitted['feedback_message'], 0, 500);
		$this->submitted['feedback_email_subject'] = $this->submitted['feedback_email_subject'] ? $this->submitted['feedback_email_subject'] :
		$this->plugin->getSettings()->getSalonName();
		if ($this->submitted['sms_test_number'] && $this->submitted['sms_test_message']) {
			$this->sendTestSms(
				$this->submitted['sms_test_number'],
				$this->submitted['sms_test_message']
			);
		}
		$this->submitted['sms_test_number'] = '';
		$this->submitted['sms_test_message'] = '';

		$this->submitted['onesignal_notification_message'] = !empty($this->submitted['onesignal_notification_message']) ?
		$this->submitted['onesignal_notification_message']
		:
		self::getDefaultOnesignalNotificationMessage();
	}

	protected function postProcess() {
		wp_clear_scheduled_hook('sln_sms_reminder');
		if (isset($this->submitted['sms_remind']) && $this->submitted['sms_remind']) {
			wp_schedule_event(time(), 'hourly', 'sln_sms_reminder');
			wp_schedule_event(time() + 1800, 'hourly', 'sln_sms_reminder');
		}
		wp_clear_scheduled_hook('sln_email_reminder');
		if (isset($this->submitted['email_remind']) && $this->submitted['email_remind']) {
			wp_schedule_event(time(), 'hourly', 'sln_email_reminder');
			wp_schedule_event(time() + 1800, 'hourly', 'sln_email_reminder');
		}
		if (isset($this->submitted['follow_up_sms']) && $this->submitted['follow_up_sms']) {
			if (!wp_get_schedule('sln_sms_followup')) {
				wp_schedule_event(time(), 'daily', 'sln_sms_followup');
			}
		} else {
			wp_clear_scheduled_hook('sln_sms_followup');
		}
		if (isset($this->submitted['follow_up_email']) && $this->submitted['follow_up_email']) {
			if (!wp_get_schedule('sln_email_followup')) {
				wp_schedule_event(time(), 'daily', 'sln_email_followup');
			}
		} else {
			wp_clear_scheduled_hook('sln_email_followup');
		}

		if ((isset($this->submitted['feedback_email']) && $this->submitted['feedback_email']) || (isset($this->submitted['feedback_sms']) && $this->submitted['feedback_sms'])) {
			if (!wp_get_schedule('sln_email_feedback')) {
				wp_schedule_event(time(), 'daily', 'sln_email_feedback');
			}
		} else {
			wp_clear_scheduled_hook('sln_email_feedback');
		}


		if (isset($this->submitted['editors_manage_cap']) && $this->submitted['editors_manage_cap']) {
			SLN_UserRole_SalonStaff::addCapabilitiesForRole('editor');
		} else {
			SLN_UserRole_SalonStaff::removeCapabilitiesFoRole('editor');
		}

		if (!empty($this->submitted['salon_staff_manage_cap_export_csv'])) {
			SLN_UserRole_SalonStaff::addCapabilities(array('export_reservations_csv_sln_calendar'));
		} else {
			SLN_UserRole_SalonStaff::removeCapabilities(array('export_reservations_csv_sln_calendar'));
		}
	}

    protected function sendTestSms($number, $message)
    {
		$sms = $this->plugin->sms();
		$sms->send($number, $message);
		if ($sms->hasError()) {
			$this->showAlert('error', $sms->getError());
		} else {
			$this->showAlert(
				'success',
				__('Test sms sent with success', 'salon-booking-system'),
				''
			);
		}
	}

    public static function getDefaultSmsNotificationMessage()
    {
		return "Hi [NAME],\r\ntake note of your reservation at [SALON NAME] on [DATE] at [TIME].\r\nSee you soon.";
	}

	public static function getDefaultSmsNotificationMessageModified() {
		return "Hi [NAME],\r\nyour reservation at [SALON NAME], has been updated.\r\nWe've sent you an email with the details.";
	}

    public static function getDefaultOnesignalNotificationMessage()
    {
		return __("Hi, the new reservation at [SALON NAME] on [DATE] at [TIME].", "salon-booking-system");
	}
}
?>