<?php

namespace SLB_PWA;

use SLB_API_Mobile\Helper\TokenHelper;
use SLB_API_Mobile\Helper\UserRoleHelper;

class Plugin {

    private static $instance;

    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('parse_request', array($this, 'render_page'));
    }

    public function render_page()
    {
        global $wp;

        $current_url = home_url(add_query_arg(array(), $wp->request));
        $salon_booking_pwa_url = home_url('salon-booking-pwa');

        if ($salon_booking_pwa_url !== trim($current_url, '/')) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            wp_safe_redirect( wp_login_url( home_url($_SERVER['REQUEST_URI']) ) );
            exit();
        }

        $user               = wp_get_current_user();
	$user_role_helper   = new UserRoleHelper();

        if ( ! $user_role_helper->is_allowed_user($user) ) {
            _e( 'Sorry, your user role is not allowed.', 'salon-booking-system' );
            exit();
	}

        $dist = SLN_PLUGIN_URL . '/src/SLB_PWA/pwa/dist';
        $data = array(
            'api'               => home_url('wp-json/salon/api/mobile/v1/'),
            'token'             => (new TokenHelper())->getUserAccessToken($user->ID),
            'onesignal_app_id'  => \SLN_Plugin::getInstance()->getSettings()->get('onesignal_app_id'),
            'locale'            => explode('_', \SLN_Plugin::getInstance()->getSettings()->getDateLocale())[0],
        );

        $dist_directory_path = SLN_PLUGIN_DIR . '/src/SLB_PWA/pwa/dist';

        if (!file_exists($dist_directory_path . '/js/app.template.js')) {
            file_put_contents($dist_directory_path . '/js/app.template.js', file_get_contents($dist_directory_path . '/js/app.js'));
        }

        if (!file_exists($dist_directory_path . '/js/app.js.template.map')) {
            file_put_contents($dist_directory_path . '/js/app.js.template.map', file_get_contents($dist_directory_path . '/js/app.js.map'));
        }

        if (!file_exists($dist_directory_path . '/service-worker.template.js')) {
            file_put_contents($dist_directory_path . '/service-worker.template.js', file_get_contents($dist_directory_path . '/service-worker.js'));
        }

        if (!file_exists($dist_directory_path . '/service-worker.js.template.map')) {
            file_put_contents($dist_directory_path . '/service-worker.js.template.map', file_get_contents($dist_directory_path . '/service-worker.js.map'));
        }

        if (!file_exists($dist_directory_path . '/index.template.html')) {
            file_put_contents($dist_directory_path . '/index.template.html', file_get_contents($dist_directory_path . '/index.html'));
        }

        $dist_url_path = trim(str_replace(home_url(), '', $dist), '/');

        file_put_contents($dist_directory_path . '/js/app.js', str_replace('{SLN_PWA_DIST_PATH}', $dist_url_path, file_get_contents($dist_directory_path . '/js/app.template.js')));
        file_put_contents($dist_directory_path . '/js/app.js.map', str_replace('{SLN_PWA_DIST_PATH}', $dist_url_path, file_get_contents($dist_directory_path . '/js/app.js.template.map')));
        file_put_contents($dist_directory_path . '/service-worker.js', str_replace('{SLN_PWA_DIST_PATH}', $dist_url_path, file_get_contents($dist_directory_path . '/service-worker.template.js')));
        file_put_contents($dist_directory_path . '/service-worker.js.map', str_replace('{SLN_PWA_DIST_PATH}', $dist_url_path, file_get_contents($dist_directory_path . '/service-worker.js.template.map')));
        file_put_contents($dist_directory_path . '/index.html', str_replace(array('{SLN_PWA_DIST_PATH}', '{SLN_PWA_DATA}'), array($dist_url_path, json_encode($data)), file_get_contents($dist_directory_path . '/index.template.html')));

    ?>
    <!doctype html>
    <html lang="">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <!--[if IE]><link rel="icon" href="<?php echo $dist ?>/favicon.ico"><![endif]-->
            <title>salon-booking-plugin-pwa</title>
            <script defer="defer" src="<?php echo $dist ?>/js/chunk-vendors.js"></script>
            <script defer="defer" src="<?php echo $dist ?>/js/app.js"></script>
            <link href="<?php echo $dist ?>/css/chunk-vendors.css" rel="stylesheet">
            <link href="<?php echo $dist ?>/css/app.css" rel="stylesheet">
            <link rel="icon" type="image/svg+xml" href="<?php echo $dist ?>/img/icons/favicon.svg">
            <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $dist ?>/img/icons/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $dist ?>/img/icons/favicon-16x16.png">
            <link rel="manifest" href="<?php echo $dist ?>/manifest.json">
            <meta name="theme-color" content="#ffd100">
            <meta name="apple-mobile-web-app-capable" content="no">
            <meta name="apple-mobile-web-app-status-bar-style" content="default">
            <meta name="apple-mobile-web-app-title" content="Salon Booking Plugin">
            <link rel="apple-touch-icon" href="<?php echo $dist ?>/img/icons/apple-touch-icon-152x152.png">
            <link rel="mask-icon" href="<?php echo $dist ?>/img/icons/safari-pinned-tab.svg" color="#ffd100">
            <meta name="msapplication-TileImage" content="<?php echo $dist ?>/img/icons/msapplication-icon-144x144.png">
            <meta name="msapplication-TileColor" content="#000000">
        </head>
        <style>
            .free-version-wrapper {
                text-align: center;
                padding: 20px;
                background-color: #ecf1fa9b;
                margin: 10px;
            }
            .free-version-button {
                background-color: #0d6efd;
                color: #fff;
                padding: 6px 12px;
                border-radius: 4px;
                text-decoration: none;
                margin-top: 10px;
                display: inline-block;
            }
        </style>
        <body>
            <noscript>
                <strong>We're sorry but salon-booking-plugin-pwa doesn't work properly without JavaScript enabled. Please enable it to continue.</strong></noscript>
            <script>
                var slnPWA = JSON.parse('<?php echo json_encode($data) ?>')
            </script>
            <?php if ( ! defined('SLN_VERSION_PAY') ):  ?>
                <div class="free-version-wrapper">
                    <p><?php echo sprintf(__('Dear <b>%s</b>,<br/> to use our mobile app you need a PRO version of <b>Salon Booking System</b>', 'salon-booking-system'), $user->display_name); ?></p>
                    <p><a href="https://www.salonbookingsystem.com/plugin-pricing/" target="_blank" class="free-version-button"><?php  _e('Switch to PRO', 'salon-booking-system') ?></a></p>
                </div>
            <?php else: ?>
                <div id="app"></div>
            <?php endif; ?>
        </body>
    </html>
    <?php
        exit();
    }
}