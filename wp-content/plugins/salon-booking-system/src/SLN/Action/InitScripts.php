<?php

class SLN_Action_InitScripts {
	const ASSETS_VERSION = SLN_VERSION;
	private static $isInclude = false;
	private $isAdmin;
	private $plugin;

	public function __construct(SLN_Plugin $plugin, $isAdmin) {
		$this->plugin = $plugin;
		$this->isAdmin = $isAdmin;

		if ($isAdmin) {
			add_action('admin_enqueue_scripts', array($this, 'hook_enqueue_scripts'));
			add_action('wp_print_scripts', array($this, 'hook_admin_print_scripts'));
			add_action('sln.view.settings.checkout.additional_fields', [$this, 'enqueueCustomFieldEditor']);
			if (SLN_Func::isSalonPage()) {
				add_filter('script_loader_src', array($this, 'hook_script_loader_src'), 10, 2);
				add_filter('style_loader_src', array($this, 'hook_script_loader_src'), 10, 2);
			}
		}
		add_action('wp_enqueue_scripts', array($this, 'hook_enqueue_scripts'), 99999);
	}

        public function hook_enqueue_scripts() {

            global $post;

            /**

             * Betheme compatibility

             */
            $mfnCmpHasShortcode = false;
            if (!$this->isAdmin && is_a($post, 'WP_Post') && defined('MFN_THEME_VERSION')) {
                $mfn_builder = new \Mfn_Builder_Front(get_the_ID());
                ob_start();
                $mfn_builder->show();
                $content = ob_get_clean();
                $mfnCmpHasShortcode = strpos($content, SLN_Shortcode_Salon::NAME)

                    || strpos($content, SLN_Shortcode_SalonMyAccount::NAME)

                    || strpos($content, SLN_Shortcode_SalonCalendar::NAME)

                    || strpos($content, SLN_Shortcode_SalonAssistant::NAME)

                    || strpos($content, SLN_Shortcode_SalonServices::NAME)

                    || strpos($content, SLN_Shortcode_SalonRecentComments::NAME);
            }

            if (
                !$this->isAdmin && is_a( $post, 'WP_Post' ) && (

                    has_shortcode( $post->post_content, SLN_Shortcode_Salon::NAME )

                    || has_shortcode( $post->post_content, SLN_Shortcode_SalonMyAccount::NAME )

                    || has_shortcode( $post->post_content, SLN_Shortcode_SalonCalendar::NAME )

                    || has_shortcode( $post->post_content, SLN_Shortcode_SalonAssistant::NAME )

                    || has_shortcode( $post->post_content, SLN_Shortcode_SalonServices::NAME )

                    || has_shortcode( $post->post_content, SLN_Shortcode_SalonRecentComments::NAME)


                    /**

                    * Unyson compatibility

                    */

                    || (has_shortcode( $post->post_content, 'text_block' ) && ($text_attr = shortcode_parse_atts($post->post_content)['text']) && (

                    strpos( $text_attr, SLN_Shortcode_Salon::NAME )

                    || strpos( $text_attr, SLN_Shortcode_SalonMyAccount::NAME )

                    || strpos( $text_attr, SLN_Shortcode_SalonCalendar::NAME )

                    || strpos( $text_attr, SLN_Shortcode_SalonAssistant::NAME )

                    || strpos( $text_attr, SLN_Shortcode_SalonServices::NAME )

                    || strpos( $text_attr, SLN_Shortcode_SalonRecentComments::NAME )

                    ))

                    || $mfnCmpHasShortcode
                )

            ) {
				self::$isInclude = true;

                self::preloadScripts();

                self::enqueueTwitterBootstrap(false);

                $this->preloadFrontendScripts();

            }
        }

	public static function preloadEnqueueScript(){
		if(!self::$isInclude){
			self::$isInclude = true;
			self::preloadScripts();
			self::enqueueTwitterBootstrap(false);
			self::preloadFrontendScripts();
		}
	}

	public static function preloadScripts() {
		wp_enqueue_script(
			'salon-raty',
			SLN_PLUGIN_URL . '/js/jquery.raty.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
		wp_enqueue_script('salon', SLN_PLUGIN_URL . '/js/salon.js', array('jquery', 'salon-raty'), self::ASSETS_VERSION, true);
		self::enqueueGoogleMapsApi();
		$s = SLN_Plugin::getInstance()->getSettings();
		$lang = $s->getLocale();

		$params = array(
			'ajax_url' => admin_url('admin-ajax.php') . '?lang=' . $lang,
			'ajax_nonce' => wp_create_nonce('ajax_post_validation'),
			'loading' => SLN_PLUGIN_URL . '/img/preloader.gif',
			'txt_validating' => __('checking availability', 'salon-booking-system'),
			'images_folder' => SLN_PLUGIN_URL . '/img',
			'confirm_cancellation_text' => __('Do you really want to cancel?', 'salon-booking-system'),
			'time_format' => SLN_Enum_TimeFormat::getJSFormat(
				$s->get('time_format')
			),
			'has_stockholm_transition' => self::hasStockholmTransition() ? 'yes' : 'no',
			'checkout_field_placeholder' => __('fill this field', 'salon-booking-system'),
			'txt_close' => __('Close', 'salon-booking-system'),
		);

		$fbLoginEnabled = SLN_Plugin::getInstance()->getSettings()->get('enabled_fb_login');
		$fbAppID = SLN_Plugin::getInstance()->getSettings()->get('fb_app_id');
		if ($fbLoginEnabled && !empty($fbAppID)) {
			$params['fb_app_id'] = $fbAppID;

			$tmpFbLocale = explode('-', get_bloginfo('language'));
			$params['fb_locale'] = $tmpFbLocale ? $tmpFbLocale[0] . '_' . strtoupper(isset($tmpFbLocale[1]) ? $tmpFbLocale[1] : $tmpFbLocale[0]) : 'en_US';
		}

		wp_localize_script(
			'salon',
			'salon',
			$params
		);

                self::enqueueIntlTelInput();
	}

	private static function enqueueSalonMyAccount() {
		wp_enqueue_script(
			'salon-my-account',
			SLN_PLUGIN_URL . '/js/salon-my-account.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);

                wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tooltip');

		$l10n = array(
			'success' => __('Profile updated successfully.', 'salon-booking-system'),
		);
		wp_localize_script('salon-my-account', 'salonMyAccount_l10n', $l10n);
	}

	public function enqueueCustomFieldEditor() {
		wp_register_script('sortable', SLN_PLUGIN_URL . '/js/Sortable.min.js',
			array('jquery'),
			self::ASSETS_VERSION, true);
		wp_enqueue_script('custom_field_editor', SLN_PLUGIN_URL . '/js/admin/customFieldEditor.js',
			array('jquery', 'sortable'),
			self::ASSETS_VERSION,
			true);
		wp_add_inline_script('custom_field_editor', 'var sln_getFieldDefault = function(){ return ' . json_encode(SLN_Enum_CheckoutFields::$default_atts) . ';
        }', 'before');
		wp_localize_script('custom_field_editor', 'salonCheckoutFieldsEditor_l10n', [
			'add_field' => 'Add Field',
			'update_field' => 'Update Field',

		]);
	}

	private static function hasStockholmTransition() {
		global $qode_options;

		return $qode_options && $qode_options['page_transitions'] > 0;
	}

	private static function preloadFrontendScripts() {
		self::enqueueDateTimePicker();
		self::enqueueSelect2();
		wp_enqueue_style('salon-booking-calendar-shortcode', SLN_PLUGIN_URL . '/css/booking-calendar-shortcode/css/style.css', array(), self::ASSETS_VERSION, 'all');
		//Rtl support
		wp_style_add_data('salon-booking-calendar-shortcode', 'rtl', 'replace');
		wp_enqueue_style('salon', SLN_PLUGIN_URL . '/css/salon.css', array(), self::ASSETS_VERSION, 'all');
		//Rtl support
		wp_style_add_data('salon', 'rtl', 'replace');
		if (SLN_Plugin::getInstance()->getSettings()->get('style_colors_enabled')) {
			$dir = wp_upload_dir();
			$dir = $dir['baseurl'];
			if (is_ssl()) {
				$dir = str_replace('http://', 'https://', $dir);
			}

			wp_enqueue_style('sln-custom', $dir . '/sln-colors.css', array(), self::ASSETS_VERSION, 'all');
		}
		self::enqueueSalonMyAccount();
	}

	public static function enqueueCustomSliderRange() {
		wp_enqueue_script(
			'salon-customSliderRange',
			SLN_PLUGIN_URL . '/js/admin/customSliderRange.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
		//100% we need this too
		wp_enqueue_script(
			'salon-customRulesCollections',
			SLN_PLUGIN_URL . '/js/admin/customRulesCollections.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);

	}

	public static function enqueueServiceBreakSliderRange() {
		wp_enqueue_script(
			'salon-sliderRange',
			SLN_PLUGIN_URL . '/js/jquery.ui.slider.dragRange.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
		wp_enqueue_script(
			'salon-serviceBreakSliderRange',
			SLN_PLUGIN_URL . '/js/admin/serviceBreakSliderRange.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
                wp_localize_script('salon-serviceBreakSliderRange', 'sln_SliderDragRange', [
                    'break_string' => __('break', 'sln-booking-system'),
		]);
	}

	public static function enqueueCustomMetaService() {
		wp_enqueue_script(
			'salon-customMetaService',
			SLN_PLUGIN_URL . '/js/admin/customMetaService.js',
			array('jquery'),
			SLN_Action_InitScripts::ASSETS_VERSION,
			true
		);
	}

	public static function enqueueCustomBookingUser() {
		wp_enqueue_script(
			'salon-customBookingUser',
			SLN_PLUGIN_URL . '/js/admin/customBookingUser.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
		wp_localize_script('salon-customBookingUser', 'sln_customBookingUser', array(
			'not_available_string' => __('Could be not available', 'sln-booking-system'),
		));
	}

	public static function enqueueTwitterBootstrap($force = true) {
		$s = SLN_Plugin::getInstance()->getSettings();
		if ($force || !$s->get('no_bootstrap')) {
			wp_enqueue_style(
				'salon-bootstrap',
				SLN_PLUGIN_URL . '/css/sln-bootstrap.css',
				array(),
				self::ASSETS_VERSION,
				'all'
			);
			//Rtl support
			wp_style_add_data('salon-bootstrap', 'rtl', 'replace');
		}
		if ($force || !$s->get('no_bootstrap_js')) {
			wp_enqueue_script(
				'salon-bootstrap',
				SLN_PLUGIN_URL . '/js/bootstrap.min.js',
				array('jquery'),
				self::ASSETS_VERSION,
				true
			);
		}
	}

	public static function enqueueGoogleMapsApi() {
		$settings = SLN_Plugin::getInstance()->getSettings();
		if ($settings->get('google_maps_api_key') && $settings->get('google_maps_api_key_valid')) {
			do_action('sln_before_enqueue_googlemapsapi');
			wp_enqueue_script('salon-google-maps', '//maps.googleapis.com/maps/api/js?key=' . $settings->get('google_maps_api_key') . '&libraries=places,drawing' . '&callback=sln_google_maps_places_api_callback',
				array('salon'), null, 'in_footer'); //drawing library enabled for compatibility with shop georef addon
		}
	}

	public static function enqueueDateTimePicker() {
		$date_lang = SLN_Plugin::getInstance()->getSettings()->getDateLocale();

		wp_enqueue_script(
			'smalot-datepicker',
			SLN_PLUGIN_URL . '/js/bootstrap-datetimepicker.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
		if (substr($date_lang, 0, 2) != 'en') {
			wp_enqueue_script(
				'smalot-datepicker-lang',
				SLN_PLUGIN_URL . '/js/datepicker_language/bootstrap-datetimepicker.' . $date_lang . '.js',
				array('jquery', 'smalot-datepicker'),
				self::ASSETS_VERSION,
				true
			);
		}
	}

	public static function enqueueColorPicker() {
		// COLOR PICKER
		wp_enqueue_script(
			'salon-colorpicker-js',
			SLN_PLUGIN_URL . '/js/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
		wp_enqueue_style(
			'salon-colorpicker-css',
			SLN_PLUGIN_URL . '/js/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css',
			array(),
			self::ASSETS_VERSION,
			'all'
		);

	}
	public static function enqueueSettingsNavigation() {
		wp_enqueue_script(
			'salon-adminSettingsNavigation-js',
			SLN_PLUGIN_URL . '/js/admin/adminSettingsNavigation.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
	}

	public static function enqueueSelect2() {

		if (is_admin()) {
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-button');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-slider');
			wp_enqueue_script('jquery-ui-tooltip');
			wp_enqueue_script('jquery-ui-datepicker');
		}

		wp_enqueue_script('salon-admin-select2', SLN_PLUGIN_URL . '/js/select2.min.js?scope=sln', array('jquery'), SLN_VERSION, true);
		wp_enqueue_style('salon-admin-select2-css', SLN_PLUGIN_URL . '/css/select2.min.css?scope=sln', array(), SLN_VERSION, 'all');
		//Rtl support
		wp_style_add_data('salon-admin-select2-css', 'rtl', 'replace');
		wp_enqueue_script(
			'salon-customSelect2',
			SLN_PLUGIN_URL . '/js/admin/customSelect2.js?scope=sln',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);
	}

	public static function enqueueAdmin() {
		self::preloadScripts();
		self::enqueueDateTimePicker();
		wp_enqueue_script(
			'salon-customDateTime',
			SLN_PLUGIN_URL . '/js/admin/customDateTime.js',
			array('jquery'),
			self::ASSETS_VERSION,
			true
		);

		wp_enqueue_style('salon-admin-css', SLN_PLUGIN_URL . '/css/admin.css', array(), SLN_VERSION, 'all');

		//Rtl support
		wp_style_add_data('salon-admin-css', 'rtl', 'replace');

		wp_enqueue_script('salon-admin-js', SLN_PLUGIN_URL . '/js/admin.js', array('jquery'), self::ASSETS_VERSION, true);
		wp_localize_script('salon-admin-js', 'salon_admin', array(
			'i18n_mon_decimal_error' => sprintf(esc_html__('Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'salon-booking-system'), SLN_Plugin::getInstance()->getSettings()->get('pay_decimal_separator')),
			'mon_decimal_point' => SLN_Plugin::getInstance()->getSettings()->get('pay_decimal_separator'),

		));
	}
	public function hook_admin_print_scripts() {

		if (
			(is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php'))
			&& SLN_Func::isSalonPage()
		) {
			self::dequeueYoast();
		}
	}

	public function hook_script_loader_src($src, $handle) {
		if (!preg_match('/\/woocommerce\//', $src) && !preg_match('/\/select2\./', $src) || preg_match('/scope=sln/', $src)) {
			return $src;
		}
	}

	public static function dequeueYoast() {
		$scripts = array(
			'yoast-social-preview', 'wp-seo-premium-redirect-notifications', 'wp-seo-premium-custom-fields-plugin', 'yoast-seo-premium-metabox', 'yoast-seo-admin-script', 'yoast-seo-admin-media', 'yoast-seo-bulk-editor', 'yoast-seo-dismissible', 'yoast-seo-admin-global-script', 'yoast-seo-metabox', 'yoast-seo-featured-image', 'yoast-seo-admin-gsc', 'yoast-seo-post-scraper', 'yoast-seo-term-scraper', 'yoast-seo-replacevar-plugin', 'yoast-seo-shortcode-plugin', 'yoast-seo-recalculate', 'yoast-seo-primary-category', 'yoast-seo-select2', 'yoast-seo-select2-translations', 'yoast-seo-configuration-wizard');
		foreach ($scripts as $s) {
			wp_dequeue_script($s);
		}
	}

        public static function enqueueIntlTelInput() {
                wp_enqueue_script('salon-intl-tel-input', SLN_PLUGIN_URL . '/js/intl-tel-input/build/js/intlTelInput.js', array('jquery'), self::ASSETS_VERSION, true);
                wp_enqueue_style('salon-intl-tel-input', SLN_PLUGIN_URL . '/js/intl-tel-input/build/css/intlTelInput.min.css', array(), self::ASSETS_VERSION, 'all');
	}
}
