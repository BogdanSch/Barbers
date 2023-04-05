<?php

class SLN_Shortcode_Salon_DetailsStep extends SLN_Shortcode_Salon_AbstractUserStep
{
    protected function dispatchForm()
    {
        global $current_user;

	if (isset($_GET['sln_action']) && $_GET['sln_action'] === 'fb_login' && $this->getPlugin()->getSettings()->get('enabled_fb_login')) {

	    if (isset($_GET['code'])) {

            $accessToken = SLN_Helper_FacebookLogin::getAccessTokenByCode($_GET['code']);

            $tmp_values = $this->dispatchAuthFB($accessToken);

            if ( ! $this->hasErrors() ) {
                $update = [
                    'ID' => $current_user->ID,
                ];
                if(isset($values['firstname'])){
                    $update['first_name'] = $tmp_values['firstname'];
                }
                if(isset($values['lastname'])){
                    $update['last_name'] = $tmp_values['lastname'];
                }
                wp_update_user( $update );
            }

            $_SESSION['fb_access_token'] = $accessToken;

            $redirectUrl = isset($_GET['state']) ? $_GET['state'] : '';

            wp_redirect($redirectUrl);

            exit();
	    }

	    $redirectUrl = isset($_GET['referrer']) ? urlencode($_GET['referrer']) : '';
	    $fbLoginUrl  = SLN_Helper_FacebookLogin::getFacebookLoginUrl($redirectUrl);

	    wp_redirect($fbLoginUrl);

	    exit();
	}

	    if (isset($_SESSION['fb_access_token'])) {

		$tmp_values = $this->dispatchAuthFB($_SESSION['fb_access_token']);

		if ($this->hasErrors()) {
		    return false;
		}
    		$update = [
                'ID' => $current_user->ID,
            ];
            if(isset($values['firstname'])){
                $update['first_name'] = $tmp_values['firstname'];
            }
            if(isset($values['lastname'])){
                $update['last_name'] = $tmp_values['lastname'];
            }
            wp_update_user( $update );

		$values = array(
		    'fb_id' => $tmp_values['fb_id'],
		);

            foreach ( SLN_Enum_CheckoutFields::defaults() as $key => $field){
                if(!$field->isHidden()){
                    $values[$key] = $tmp_values[$key];
		}
		}

		do_action('sln.shortcode.details.dispatchForm.after_fb_login', $values, $this);

		unset($_SESSION['fb_access_token']);

	    } elseif (isset($_POST['login_name'])) {
            $ret = $this->dispatchAuth(sanitize_text_field(wp_unslash($_POST['login_name'])),sanitize_text_field($_POST['login_password']));
            if (!$ret) {
                return false;
            }

            foreach ( SLN_Enum_CheckoutFields::forRegistration()->appendSmsPrefix() as $key => $field){
                $values[$key] = $field->getValue(get_current_user_id());
            }

	    do_action('sln.shortcode.details.dispatchForm.after_login', $values, $this);
            $this->bindValues($values);
            $this->validate($values);
            if ($this->getErrors()) {
                $this->bindValues($values);
                return false;
            }else{
                $_SESSION['sln_sms_dontcheck'] = true;
            }
        } else {

            if ( empty( $_POST['sln'] ) ) {
                return false;
            }

            $values = $_POST['sln'];
            $this->bindValues($values);
            if (!is_user_logged_in()) {
                unset($_SESSION['sln_detail_step_need_register_user']);
                $this->validate($values);
                if ($this->getErrors()) {
                    return false;
                }

                if ($this->getPlugin()->getSettings()->get('enabled_force_guest_checkout') || $this->getPlugin()->getSettings()->get('enabled_guest_checkout') && isset($values['no_user_account']) && $values['no_user_account']) {
                    $_SESSION['sln_detail_step'] = $values;
		            do_action('sln.shortcode.details.dispatchForm.guest_checkout', $values, $this);
                } else {
                    if (email_exists($values['email'])) {
                        $this->addError(__('E-mail exists', 'salon-booking-system'));
                        if ($this->getErrors()) {
                            return false;
                        }
                    }

                    if ($values['password'] != $values['password_confirm']) {
                        $this->addError(__('Passwords are different', 'salon-booking-system'));
                        if ($this->getErrors()) {
                            return false;
                        }
                    }

                    if(!$this->getShortcode()->needSms()) {
                        if ($this->successRegistration($values) === false) {
			                return false;
			            }
                    }else{
			            $_SESSION['sln_detail_step_need_register_user'] = true;
                        $_SESSION['sln_detail_step'] = $values;
                    }
                }
            }else{
                $update = [
                    'ID' => $current_user->ID,
                ];
                if(isset($values['firstname'])){
                    $update['first_name'] = $values['firstname'];
                }
                if(isset($values['lastname'])){
                    $update['last_name'] = $values['lastname'];
                }
                wp_update_user( $update );

                $user_meta_fields = SLN_Enum_CheckoutFields::forRegistration()->appendSmsPrefix()->keys();
                foreach($user_meta_fields as $k){
                    if(in_array($k,['firstname','lastname'])) continue;
                    if (SLN_Enum_CheckoutFields::getField($k) && SLN_Enum_CheckoutFields::getField($k)->get('type') === 'file' && isset($values[$k]) && is_array($values[$k])) {
                        $data = array_map(function($file) {
                            return array(
                                'subdir' => wp_upload_dir()['subdir'],
                                'file'   => $file,
                            );
                        }, $values[$k]);
                        update_user_meta($current_user->ID, '_sln_'.$k, $data);
                    } else {
                        if(isset($values[$k])){
                           update_user_meta($current_user->ID, '_sln_'.$k, $values[$k]);
                        }
                    }
                }
		        do_action('sln.shortcode.details.dispatchForm.logged_checkout', $values, $this);
            }
        }

	if ( empty( $values ) ) {
	    return false;
	}

	if ( ! apply_filters('sln.shortcode.details.dispatchForm.validate_bind_values', true, $values, $this) ) {
	    return false;
	}

        $this->bindValues($values);

        return true;
    }

    private function dispatchAuthFB($accessToken) {

	try {

	    $userID = SLN_Helper_FacebookLogin::getUserIDByAccessToken($accessToken, true);

	    $user = get_user_by('id', $userID);

	    wp_set_auth_cookie($userID);
	    wp_set_current_user($userID);

	    $tmp = explode(' ', $user->display_name);

	    $tmp_lastname  = array_pop($tmp);
	    $tmp_firstname = implode(' ', $tmp);

	    $firstname = $user->user_firstname ? $user->user_firstname : $tmp_firstname;
	    $lastname  = $user->user_lastname ? $user->user_lastname : $tmp_lastname;

	    return array(
		'fb_id'     => get_user_meta($userID, '_sln_fb_id', true),
		'firstname' => $firstname,
		'lastname'  => $lastname,
		'email'     => $user->user_email,
		'phone'     => get_user_meta($userID, '_sln_phone', true),
		'address'   => '',
	    );

	} catch (\Exception $ex) {
	    $this->addError($ex->getMessage());
	}

	return array();
    }

    private function validate($values){
        $fields = SLN_Enum_CheckoutFields::forDetailsStep();

        foreach ($fields as $key => $field) {
            if ($field->isRequiredNotHidden() && empty($values[$key])){
                $this->addError(esc_html__($field['label'].' can\'t be empty', 'salon-booking-system'));
            }
        }

        $email = SLN_Enum_CheckoutFields::getField('email');
        if ($email->isRequired() && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
                $this->addError(__('e-mail is not valid', 'salon-booking-system'));
            }
        }

    public function render()
    {
        $bb = $this->getPlugin()->getBookingBuilder();
        $custom_url = apply_filters('sln.shortcode.details.render.custom_url', false, $this, $bb);
        if ($custom_url) {
            $this->redirect($custom_url);
        } else {
            return parent::render();
        }
    }

    public function redirect($url)
    {
        if ($this->isAjax()) {
            throw new SLN_Action_Ajax_RedirectException($url);
        } else {
            wp_redirect($url);
        }
    }

    private function isAjax()
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }
}
