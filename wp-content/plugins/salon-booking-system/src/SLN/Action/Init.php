<?php

class SLN_Action_Init
{
    private $plugin;

    public function __construct(SLN_Plugin $plugin)
    {
        $this->plugin = $plugin;
        add_action('init',function(){
            $this->initEnum();
            if (is_admin()) {
                $this->initAdmin();
            } else {
                $this->initFrontend();
            }
        });
        $this->init();
    }

    function initEnum(){
        SLN_Enum_BookingStatus::init();
        SLN_Enum_CheckoutFields::init();
        SLN_Enum_DateFormat::init();
        SLN_Enum_DaysOfWeek::init();
        SLN_Enum_PaymentDepositType::init();
        if(class_exists('SLN_Enum_PaymentMethodProvider')){
            SLN_Enum_PaymentMethodProvider::addService('paypal', 'PayPal', 'SLN_PaymentMethod_Paypal');
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                SLN_Enum_PaymentMethodProvider::addService('stripe', 'Stripe', 'SLN_PaymentMethod_Stripe');
            }
        }
        SLN_Enum_SmsProvider::init();
        SLN_Enum_TimeFormat::init();
    }

    private function init()
    {
        $p = $this->plugin;
        if(!defined("SLN_VERSION_CODECANYON") && defined("SLN_VERSION_PAY") && SLN_VERSION_PAY ) { $this->initLicense(); }

        if(!defined("SLN_VERSION_PAY")){
            $freemius = new SLN_Action_InitFreemius;
            $freemius->load();
        }

        new SLN_TaxonomyType_ServiceCategory(
            $p,
            SLN_Plugin::TAXONOMY_SERVICE_CATEGORY,
            array(SLN_Plugin::POST_TYPE_SERVICE)
        );
        $this->initSchedules();

        add_action('template_redirect', array($this, 'template_redirect'));
        new SLN_Privacy();
        new SLN_Action_InitScripts($this->plugin, is_admin());
        $this->initPolylangSupport();
        SLB_Discount_Plugin::getInstance();

        add_action('init', array($this, 'hook_action_init'));
        if (!SLN_Action_Install::isInstalled()) {
            add_action('init', function(){
                SLN_Action_Install::execute();
            });
        }

	if (is_admin()) {
	    new SLN_Welcome($p);
	}

	if(defined("SLN_VERSION_CODECANYON")){
            new SLN_Action_InitEnvatoAutomaticPluginUpdate();
        }

	add_action( 'profile_update', array($this, 'updateProfileLastUpdateTime') );

        new SLN_Action_UpdatePhoneCountryDialCode($p);
    }


    private function initAdmin()
    {
        $p = $this->plugin;
        new SLN_Metabox_Service($p, SLN_Plugin::POST_TYPE_SERVICE);
        new SLN_Metabox_Attendant($p, SLN_Plugin::POST_TYPE_ATTENDANT);
        new SLN_Metabox_Booking($p, SLN_Plugin::POST_TYPE_BOOKING);
        new SLN_Metabox_BookingActions($p, SLN_Plugin::POST_TYPE_BOOKING);

        new SLN_Admin_Calendar($p);
        new SLN_Admin_Tools($p);
        new SLN_Admin_Customers($p);
        new SLN_Admin_Reports($p);
        new SLN_Admin_Settings($p);

        add_action('admin_init', array($this, 'hook_admin_init'));
        $this->initAjax();
        new SLN_Action_InitComments($p);

	if (!current_user_can('delete_permanently_sln_booking')) {
	    $this->disablePermanentlyDeleteBookings();
	}

    add_action( 'admin_menu', function () {
        if ( in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            remove_menu_page( 'edit.php' );                   //Posts
            remove_menu_page( 'edit-comments.php' );          //Comments
            remove_menu_page( 'tools.php' );                  //Tools
            remove_menu_page( 'profile.php' );                  //Profile
            remove_menu_page( 'index.php' );                  //Dashboard
            remove_submenu_page( 'salon', 'edit.php?post_type=sln_service' ); //Services Salon menu
            remove_submenu_page( 'salon', 'edit-tags.php?taxonomy=sln_service_category&post_type=sln_service'); //Service Categories Salon menu
            remove_submenu_page( 'salon', 'edit.php?post_type=sln_discount'); //Discounts Salon menu
            remove_submenu_page( 'salon', SLN_Admin_Reports::PAGE); //Reports Salon menu
            remove_submenu_page( 'salon', SLN_Admin_Customers::PAGE ); //Customers Salon menu
        }
    }, 1000);

    add_action( 'admin_bar_menu', function ($wp_admin_bar) {
        if ( in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            $wp_admin_bar->remove_node( 'edit-profile' );
            $wp_admin_bar->remove_node( 'user-info' );
            $wp_admin_bar->remove_node( 'comments' );
            $wp_admin_bar->remove_node( 'new-content' );
            $wp_admin_bar->remove_node( 'view' );
        }
    }, 1000 );

    add_action('wp_before_admin_bar_render', function () {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('my-account');

        $user_id = get_current_user_id();
        $current_user = wp_get_current_user();
        if (!$user_id)
            return;

        $avatar = get_avatar($user_id, 26);
        $howdy = sprintf(__('Howdy, %s'), '<span class="display-name">' . $current_user->display_name . '</span>');
        $class = empty($avatar) ? '' : 'with-avatar';

        $wp_admin_bar->add_menu(array(
            'id' => 'my-account',
            'parent' => 'top-secondary',
            'title' => $howdy . $avatar,
            'meta' => array(
                'class' => $class,
            ),
        ));
    });

    add_action( 'current_screen', function() {
        $screen = get_current_screen();
        if ( isset( $screen->id ) && $screen->id == 'dashboard' && in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles)  ) {
            wp_redirect( admin_url( 'admin.php?page=salon' ) );
            exit();
        }
    } );

    add_filter('bulk_actions-edit-sln_attendant', function ($actions) {
        if ( in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            unset( $actions['edit'] );
	    }
        return $actions;
    }, 10, 2);

    add_filter('post_row_actions',function ($actions, $post) {
        if ( in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            unset($actions['trash']);
            unset($actions['inline hide-if-no-js']);
            unset($actions['clone']);
            unset($actions['view']);
        }
        return $actions;
    },1000,2);

    add_filter('bulk_actions-edit-sln_booking', function ($actions) {
        if ( in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            unset( $actions['edit'] );
        }
        return $actions;
    }, 10, 2);

    add_action('admin_head-post.php', function() {
        if ( in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            echo '
                <style type="text/css">
                    #misc-publishing-actions,
                    #sln_booking-notify,
                    #sln_booking-actions,
                    #post-body-content {
                        opacity: 0.5;
                        pointer-events: none;
                    }
                </style>
            ';
        }
    });

    add_action( 'load-profile.php', function() {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            wp_die(
                '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                403
            );
        }
    });

    add_action( 'load-edit-comments.php', function() {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            wp_die(
                '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                403
            );
        }
    });

    add_action( 'load-comment.php', function() {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            wp_die(
                '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                403
            );
        }
    });

    add_action( 'load-edit.php', function() {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) && empty($_GET['post_type']) ) {
            wp_die(
                '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                403
            );
        }
    });

    add_action( 'load-post.php', function() {
        $postID = isset($_GET['post']) ? $_GET['post'] : (isset($_POST['post_ID']) ? $_POST['post_ID'] : 0);
        $post = get_post($postID);
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) && (!$post || !in_array($post->post_type, array('sln_attendant', 'sln_booking')))) {
            wp_die(
                '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                403
            );
        }

        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) && $post && $post->post_type === 'sln_attendant' && get_post_meta($post->ID, '_sln_attendant_staff_member_id', true) != get_current_user_id() ) {
            wp_die(
                '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                403
            );
        }

        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) && $post && $post->post_type === 'sln_booking' ) {

            $repo	    = SLN_Plugin::getInstance()->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
            $attendants = $repo->getAll();

            foreach ($attendants as $attendant) {
                if ($attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly()) {
                $assistantsIDs[] = $attendant->getId();
                }
            }

            if (!array_filter(get_post_meta($post->ID, '_sln_booking_services', true), function($item) use($assistantsIDs) { return in_array($item['attendant'], $assistantsIDs); }) ) {
                wp_die(
                    '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                    403
                );
            }
        }
    });

    add_action( 'load-post-new.php', function() {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            wp_die(
                '<p>' . __( 'Sorry, you are not allowed access to this page.' ) . '</p>',
                403
            );
        }
    });

    add_filter( "views_edit-sln_attendant", function ($views) {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {
            return array();
        }
        return $views;
    });

    add_filter( 'wp_count_posts', function ($counts, $type, $perm) {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) && $type === 'sln_booking' ) {

            global $wpdb;

            if ( ! post_type_exists( $type ) ) {
                return new stdClass;
            }

		    $cache_key = _count_posts_cache_key( $type, $perm );

		    $assistantsIDs = array();

            $repo	    = SLN_Plugin::getInstance()->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
            $attendants = $repo->getAll();

            foreach ($attendants as $attendant) {
                if ($attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly()) {
                    $assistantsIDs[] = $attendant->getId();
                }
            }

            $query = "SELECT p.post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} p";

            if ( ! empty( $assistantsIDs ) ) {
                $query .= " INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sln_booking_services' ";
            }

            $query .= " WHERE p.post_type = %s ";

            if ( 'readable' === $perm && is_user_logged_in() ) {
                $post_type_object = get_post_type_object( $type );
                if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
                    $query .= $wpdb->prepare(
                        " AND (p.post_status != 'private' OR ( p.post_author = %d AND p.post_status = 'private' ))",
                        get_current_user_id()
                    );
                }
            }

            if ( ! empty( $assistantsIDs ) ) {
                $query .= $wpdb->prepare(
                    " AND pm.meta_value REGEXP %s ",
                    implode('|', array_map(function ($v) {
                        return sprintf('"attendant";i:%s;', $v);
                    }, $assistantsIDs))
                );
		    }

            $query .= ' GROUP BY p.post_status';

            $results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );
            $counts  = array_fill_keys( get_post_stati(), 0 );

            foreach ( $results as $row ) {
                $counts[ $row['post_status'] ] = $row['num_posts'];
            }

            $counts = (object) $counts;
            wp_cache_set( $cache_key, $counts, 'counts' );

            return $counts;
        }
        return $counts;
    }, 10, 3);

    add_filter( 'disable_months_dropdown', function ($result, $post_type ) {
        if (  in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) && $post_type === 'sln_attendant' ) {
            return true;
        }
        return $result;
    }, 10, 2);

    }

    private function initFrontend()
    {
	add_action('parse_request', array(new SLN_Action_RescheduleBooking($this->plugin), 'execute'));
	add_action('parse_request', array(new SLN_Action_CancelBookingLink($this->plugin), 'execute'));
	add_action('parse_request', array(new SLN_Action_LinkServicesBooking($this->plugin), 'execute'));
        if (class_exists('SLN_Payment_Stripe')) {
            add_action('parse_request', array(new SLN_Payment_Stripe($this->plugin), 'execute'));
        }
    }

    private function initAjax()
    {
        $callback = array($this->plugin, 'ajax');
        //http://codex.wordpress.org/AJAX_in_Plugins
        add_action('wp_ajax_salon', $callback);
        add_action('wp_ajax_nopriv_salon', $callback);
        add_action('wp_ajax_saloncalendar', $callback);
    }

    private function initSchedules() {
        add_filter('cron_schedules', array($this, 'cron_schedules'));

        if (!wp_get_schedule('sln_email_weekly_report')) {
            SLN_TimeFunc::startRealTimezone();
            if (((int)current_time('w')) === (SLN_Enum_DaysOfWeek::MONDAY) &&
                SLN_Func::getMinutesFromDuration(current_time('H:i')) < 8*60) {

                $time  = time();
                $time -= $time % (24*60*60);
            }
            else {
                $time  = SLN_TimeFunc::strtotime("next Monday");
            }

            $time += 8 * 60 * 60; // Monday 8:00
            wp_schedule_event($time, 'weekly', 'sln_email_weekly_report');
            unset($time);
            SLN_TimeFunc::endRealTimezone();
        }

        add_action('sln_sms_reminder', 'sln_sms_reminder');
        add_action('sln_email_reminder', 'sln_email_reminder');
        add_action('sln_sms_followup', 'sln_sms_followup');
        add_action('sln_email_followup', 'sln_email_followup');
        add_action('sln_email_feedback', 'sln_email_feedback');
        add_action('sln_cancel_bookings', 'sln_cancel_bookings');
        add_action('sln_email_weekly_report', 'sln_email_weekly_report');
        add_action('sln.helper.calendar_link.remove', array('SLN_Helper_CalendarLink', 'cronUnlinkCall'));

	if ( ! wp_get_schedule('sln_clean_up_database') ) {
	    wp_schedule_event(time(), 'daily', 'sln_clean_up_database');
	}

	add_action('sln_clean_up_database', 'sln_clean_up_database');
    }

    public function hook_action_init()
    {
        $p = $this->plugin;
        SLN_Shortcode_Salon::init($p);
        SLN_Shortcode_SalonMyAccount::init($p);
        SLN_Shortcode_SalonMyAccount_Details::init($p);
        SLN_Shortcode_SalonCalendar::init($p);
        SLN_Shortcode_SalonAssistant::init($p);
        SLN_Shortcode_SalonServices::init($p);
        SLN_Shortcode_SalonRecentComments::init($p);

        SLN_Enum_AvailabilityModeProvider::init();
        $this->plugin->addRepository(
            new SLN_Repository_BookingRepository(
                $this->plugin,
                new SLN_PostType_Booking($p, SLN_Plugin::POST_TYPE_BOOKING)
            )
        );

        $this->plugin->addRepository(
            new SLN_Repository_ServiceRepository(
                $this->plugin,
                new SLN_PostType_Service($p, SLN_Plugin::POST_TYPE_SERVICE)
            )
        );
        $this->plugin->addRepository(
            new SLN_Repository_AttendantRepository(
                $this->plugin,
                new SLN_PostType_Attendant($p, SLN_Plugin::POST_TYPE_ATTENDANT)
            )
        );
    }

    public function hook_admin_init()
    {
        new SLN_Action_Update($this->plugin);
    }

    public function initPolylangSupport()
    {
        add_filter('pll_get_post_types', array($this, 'hook_pll_get_post_types'));
    }

    public function hook_pll_get_post_types($types)
    {
        unset ($types['sln_booking']);
        //decomment this to have "single language services and attendant
        //unset($types['sln_service']);
        //unset($types['sln_attendant']);

        return $types;
    }

    public function template_redirect() {
        $customerHash = isset($_GET['sln_customer_login']) ? sanitize_text_field(wp_unslash( $_GET['sln_customer_login'] )) : '';
        $feedback_id = isset($_GET['feedback_id']) ? sanitize_text_field(wp_unslash($_GET['feedback_id'])) : '';
        if (!empty($customerHash)) {
            $userid = SLN_Wrapper_Customer::getCustomerIdByHash($customerHash);
            if ($userid && get_transient("sln_customer_login_{$userid}") === $customerHash) {
                $user = get_user_by('id', (int) $userid);
                if ($user) {
                    $customer = new SLN_Wrapper_Customer($user);
                    if (!$customer->isEmpty()) {
                        wp_set_auth_cookie($user->ID, false);
                        do_action('wp_login', $user->user_login, $user);

						$customer->deleteMeta('hash');
						delete_transient("sln_customer_login_{$userid}");

                        // Create redirect URL without autologin code
                        $id = $this->plugin->getSettings()->getBookingmyaccountPageId();
                        if ($id) {
                            $url = get_permalink($id);
                            if(!empty($feedback_id)) {
                                $url .= '?feedback_id='. $feedback_id;
                            }
                        }else{
                            $url = home_url();
                        }
                        wp_redirect($url);
                        exit;
                    }
                }
            }
        }
    }

    public function cron_schedules($schedules) {
        $schedules['weekly'] = array(
            'interval' => 60 * 60 * 24 * 7,
            'display' => __('Weekly', 'salon-booking-system')
        );

        return $schedules;
    }

    private function initLicense()
    {
        global $sln_license;
        /** @var SLN_Update_Manager $sln_license */
        $sln_license = new SLN_Update_Manager(
            array(
                'slug'     => SLN_ITEM_SLUG,
                'basename' => SLN_PLUGIN_BASENAME,
                'name'     => SLN_ITEM_NAME,
                'version'  => SLN_VERSION,
                'author'   => SLN_AUTHOR,
                'store'    => SLN_STORE_URL,
                'api_key'  => SLN_API_KEY,
                'api_token'=> SLN_API_TOKEN,
            )
        );
    }

    public function disablePermanentlyDeleteBookings() {

	add_filter( 'pre_delete_post', function ($check, $post, $force_delete) {
	    if ($post->post_type === SLN_Plugin::POST_TYPE_BOOKING) {
		return false;
	    }
	    return $check;
	}, 10, 3);

	if (isset($_GET['post_type']) && $_GET['post_type'] === SLN_Plugin::POST_TYPE_BOOKING) {
	    add_action( 'admin_enqueue_scripts', function () {
		wp_enqueue_style('admin-disable-delete-permanently', SLN_PLUGIN_URL.'/css/admin-disable-delete-permanently.css', array(), SLN_Action_InitScripts::ASSETS_VERSION, 'all');
	    });
	}
    }

    public function updateProfileLastUpdateTime($user_id) {
	update_user_meta($user_id, '_sln_last_update', current_time('timestamp', true));
    }
}
