<?php

class SLN_Admin_Tools extends SLN_Admin_AbstractPage
{

    const PAGE = 'salon-tools';
    const PRIORITY = 13;

    public function __construct(SLN_Plugin $plugin)
    {
        parent::__construct($plugin);
        add_action('admin_init', array($this, 'admin_init'));
	add_action('in_admin_header', array($this, 'in_admin_header'));
    }

    public function admin_init()
    {
        if (isset($_POST) && $_POST) {
            $data = $_POST;
            if (isset($data['sln-tools-import'])) {
                $this->save_settings($data);
            } elseif (isset($data['sln-tools-export'])) {
		if (current_user_can('export_reservations_csv_sln_calendar')) {
		    $this->export($data);
		}
            }
        }
    }

    public function admin_menu()
    {
        $pagename = add_submenu_page(
			'salon',
			__('Salon Tools', 'salon-booking-system'),
			__('Tools', 'salon-booking-system'),
			apply_filters('salonviews/settings/capability', 'manage_salon_settings'),
			self::PAGE,
			array($this, 'show')
		);
		add_action($pagename, array($this, 'enqueueAssets'), 0);
    }

    public function show()
    {
        if (version_compare(phpversion(), "5.4.0", "<")) {
            $info = json_encode(get_option(SLN_Settings::KEY));
        } else {
            $info = json_encode(get_option(SLN_Settings::KEY), JSON_PRETTY_PRINT);
        }

        $current_version = $this->settings->getDbVersion();

        $versionToRollback = '';
        $rollbacks         = SLN_Action_Update::getDbRollbacks();
        krsort($rollbacks);
        foreach ($rollbacks as $version => $rollback) {
            if (version_compare($current_version, $version, '>=')) {
                if (preg_match('/sln-rollback-to-(\d+[\.\d+]*).php$/', $rollback, $matches)) {
                    $versionToRollback = $matches[1];
                    break;
                }
            }
        }

        echo $this->plugin->loadView(
            'admin/tools',
            array(
                'info'              => $info,
                'versionToRollback' => $versionToRollback,
                'currentVersion'    => $current_version,
                'isFree'            => defined('SLN_Plugin::F1'),
            )
        );
    }

    public function export($data)
    {
        $format = $this->plugin->format();

        $from = $data['export']['from'];
        $from = SLN_Func::filter($from, 'date').' 00:00:00';

        $to = $data['export']['to'];
        $to = SLN_Func::filter($to, 'date').' 23:59:59';

        $criteria['@wp_query'] = array(
            'post_type'  => SLN_Plugin::POST_TYPE_BOOKING,
            'nopaging'   => true,
            'meta_query' => array(
                array(
                    'key'     => '_sln_booking_date',
                    'value'   => array($from, $to),
                    'compare' => 'BETWEEN',
		    'type'    => 'DATETIME',
                ),
            ),
        );
        $criteria['@query']    = true;

        /** @var SLN_Repository_BookingRepository $repo */
        $repo     = $this->plugin->getRepository(SLN_Plugin::POST_TYPE_BOOKING);
        $bookings = $repo->get(apply_filters('sln_tools_export_criteria', $criteria));

        usort($bookings, array($this, 'sortAscByStartsAt'));

        $checkout_fields = SLN_Enum_CheckoutFields::all()->exportCsv();

        $headers = array(
            __('ID', 'salon-booking-system'),
            __('DATE/TIME', 'salon-booking-system'),
            __('CREATED', 'salon-booking-system'),
            __('SERVICES', 'salon-booking-system'),
            __('ASSISTANTS', 'salon-booking-system'),
            __('TOTAL PRICE', 'salon-booking-system'),
            __('STATUS', 'salon-booking-system'),
        );

        $checkout_headers = array();
        foreach( $checkout_fields as $key => $field ) {
            switch ( $key ) {
                case 'firstname':
                    $checkout_headers[] = __('CUSTOMER FIRST NAME', 'salon-booking-system');
                    break;
                case 'lastname':
                    $checkout_headers[] = __('CUSTOMER LAST NAME', 'salon-booking-system');
                    break;
                case 'email':
                    $checkout_headers[] = __('CUSTOMER EMAIL', 'salon-booking-system');
                    break;
                case 'phone':
                    $checkout_headers[] = __('CUSTOMER PHONE', 'salon-booking-system');
                    break;
                case 'address':
                    $checkout_headers[] = __('CUSTOMER ADDRESS', 'salon-booking-system');
                    break;
                default:
                    $checkout_headers[] = $field->label();
                    break;
            }
        }

        array_splice( $headers, 3, 0, $checkout_headers );

        $tmpfname = tempnam(get_temp_dir(), "sln-export-");

        $fh = fopen($tmpfname, "w"); 

        fwrite( $fh, chr( 239 ) . chr( 187 ) . chr( 191 ) );

        fputcsv(
            $fh,
            array(
                __('Reservations', 'salon-booking-system'),
                __('from', 'salon-booking-system'),
                __('to', 'salon-booking-system'),
            )
        );
        fputcsv($fh, array('', $format->date($from), $format->date($to)));
        fputcsv($fh, array(''));
        fputcsv(
            $fh,
            apply_filters('sln_tools_export_headers', $headers)
        );

        foreach ($bookings as $booking) {
            $services   = $booking->getServices();
            $servicesAr = array();
            foreach ($services as $service) {
                $servicesAr[] = $service->getName();
            }

            $total = $format->money($booking->getAmount(), false, false, true, true);
            if (SLN_Enum_BookingStatus::PAID == $booking->getStatus() && $deposit = $booking->getDeposit()) {
                $total .= ' ('.$format->money($deposit, true, false, true, true).' '.
                          __('already paid as deposit', 'salon-booking-system').')';
            }

            $booking_values = array(
                $booking->getId(),
                $format->datetime($booking->getStartsAt()),
                $format->datetime($booking->getPostDate()),
                implode(', ', $servicesAr),
                $booking->getAttendantsString(),
                $total,
                SLN_Enum_BookingStatus::getLabel($booking->getStatus()),
            );

            $checkout_values = array();
            foreach( $checkout_fields as $key => $field ) {
                $checkout_value = $booking->getMeta( $key );
                if(!isset($checkout_value) || empty($checkout_value)){
                    $checkout_value = get_user_meta($booking->getUserId(), "_sln_{$key}", true);
                }
                $checkout_values[] = $checkout_value;
            }

            array_splice( $booking_values, 3, 0, $checkout_values );

            fputcsv(
                $fh,
                apply_filters('sln_tools_export_booking_values', $booking_values, $booking)
            );
        }
        fclose($fh);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"export.csv\";");
        header("Content-Transfer-Encoding: binary");
        echo file_get_contents($tmpfname);

        unlink($tmpfname);
        exit;
    }

    /**
     * @@param SLN_Wrapper_Booking $a
     * @param SLN_Wrapper_Booking $b
     *
     * @return int
     */
    private function sortAscByStartsAt($a, $b)
    {
        return ($a->getStartsAt()->getTimestamp() > $b->getStartsAt()->getTimestamp() ? 1 : -1);
    }

    public function save_settings($data)
    {
        if ( ! isset($data['sln-tools-import'])) {
            return;
        }

        $import_data = str_replace(array('\\"', '\\\\"', '\\\'', '\\\\/', '\\\\r', '\\\\n'), array('"', '\"', '\'', '\/', '\r', '\n'), $data['tools-import']);
        $import_data = json_decode($import_data, 1);
        if (is_array($import_data)) {
            update_option(SLN_Settings::KEY, $import_data);
            $this->settings->load();
            add_action('admin_notices', array($this, 'tool_admin_notice'));
        } else {
            add_action('admin_notices', array($this, 'tool_admin_error_notice'));
        }
    }

    public function tool_admin_notice()
    {
        ?>
        <div class="updated">
            <p><?php _e('Settings updated successfully!', 'salon-booking-system'); ?></p>
        </div>
        <?php
    }

    public function tool_admin_error_notice()
    {
        ?>
        <div class="error">
            <p><?php _e('You have entered the wrong data', 'salon-booking-system'); ?></p>
        </div>
        <?php
    }

}
