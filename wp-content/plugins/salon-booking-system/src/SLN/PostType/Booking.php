<?php

class SLN_PostType_Booking extends SLN_PostType_Abstract
{

    public function init()
    {
        parent::init();

        if (is_admin()) {
            add_action('manage_'.$this->getPostType().'_posts_custom_column', array($this, 'manage_column'), 10, 2);
            add_filter('manage_'.$this->getPostType().'_posts_columns', array($this, 'manage_columns'));
            add_action('admin_footer-post.php', array($this, 'bulkAdminFooterEdit'));
            add_action('admin_footer-post-new.php', array($this, 'bulkAdminFooterNew'));
            add_filter('display_post_states', array($this, 'bulkPostStates'), 10, 2);
            add_action('admin_head-post-new.php', array($this, 'posttype_admin_css'));
            add_action('admin_head-post.php', array($this, 'posttype_admin_css'));
            add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'), 10, 2);
            add_filter('parse_query', array($this, 'parse_query'));
            add_filter('pre_get_posts', array($this, 'pre_get_posts'));
            add_filter('post_row_actions', array($this, 'post_row_actions'), 10, 2);
            add_filter( 'months_dropdown_results', array($this,'months_dropdown_results'),10,2 );
            add_filter( 'posts_join', array($this, 'posts_join'),10,2 );
            add_filter( 'posts_where', array($this, 'posts_where') );
            add_filter( 'posts_distinct', array($this,'posts_distinct') );
            add_action('admin_enqueue_scripts',array($this,'admin_enqueue_scripts')   );
            add_filter( 'posts_search', [$this,'posts_search'], 10, 2 );
            add_filter('redirect_post_location', [$this, 'redirect_post_location'], 10, 2);
        }
        $this->registerPostStatus();
    }

    public function redirect_post_location($link, $post_id){
        if(isset($_POST['_wp_http_referer'])){
            parse_str(parse_url($_POST['_wp_http_referer'], PHP_URL_QUERY), $query);
            if(isset($query['mode']) && $query['mode'] === 'sln_editor'){
                $link = add_query_arg(array('mode'=> 'sln_editor'), $link);
            }
            if(isset($query['sln_editor_popup'])){
                $link = add_query_arg(array('sln_editor_popup'=> $query['sln_editor_popup']), $link);
            }
            if(isset($_POST['post_status']) &&  $_POST['post_status'] === SLN_Enum_BookingStatus::PENDING){
                if(isset($_POST['_sln_booking_status'])){
                    if($_POST['_sln_booking_status'] === SLN_Enum_BookingStatus::CONFIRMED){
                        $link = add_query_arg(array('message' => '2'), $link);
                    }else if($_POST['_sln_booking_status'] === SLN_Enum_BookingStatus::CANCELED){
                        $link = add_query_arg(array('message' => '3'), $link);
                    }
                }
            }
        }
        return $link;
    }

    public function admin_enqueue_scripts(){
        $screen = get_current_screen();
        if( $screen->id === 'edit-sln_booking' ){
            wp_enqueue_style('salon-admin-css', SLN_PLUGIN_URL.'/css/admin.css', array(), SLN_VERSION, 'all');

	    //Rtl support
	    wp_style_add_data( 'salon-admin-css', 'rtl', 'replace' );

            SLN_Action_InitScripts::preloadScripts();

            SLN_Action_InitScripts::enqueueSelect2();
            SLN_Action_InitScripts::enqueueTwitterBootstrap(true);
            SLN_Action_InitScripts::enqueueAdmin();

            ?>
            <?php if ( ! in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ): ?>
            <script>
                window.Userback = window.Userback || {};
                Userback.access_token = '33731|64310|7TOMg95VWdhaFTyY2oCZrnrV3';
                (function(d) {
                var s = d.createElement('script');s.async = true;
                s.src = 'https://static.userback.io/widget/v1.js';
                (d.head || d.body).appendChild(s);
                })(document);
            </script>
            <?php endif; ?>
            <?php
        }
    }


    function posts_search( $search, $wp_query ) {
        // Bail if we are not in the admin area
        if ( ! is_admin() ) {
            return $search;
        }

        // Bail if this is not the search query.
        if ( ! $wp_query->is_main_query() && ! $wp_query->is_search() ) {
            return $search;
        }

        // Get the value that is being searched.
        $search_string = get_query_var( 's' );

        // Bail if the search string is not an integer.
        if ( ! filter_var( $search_string, FILTER_VALIDATE_INT ) ) {
            return $search;
        }

	global $wpdb;

        // This appears to be a search using a post ID.
        // Return modified posts_search clause.
        return "AND " . $wpdb->posts . ".ID = '" . intval( $search_string )  . "'";
    }

    public function pre_get_posts($query){
        global $pagenow;

        if (isset($_GET['post_type']) && $_GET['post_type'] === $this->getPostType() && is_admin() && $pagenow=='edit.php' && $query->get('post_type') === $this->getPostType()) {
            $query->set('m', null);

	    if ( in_array(SLN_Plugin::USER_ROLE_STAFF,  wp_get_current_user()->roles) || in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ) {

		$assistantsIDs = array();

		$repo	    = $this->getPlugin()->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
		$attendants = $repo->getAll();

		foreach ($attendants as $attendant) {
		    if ($attendant->getMeta('staff_member_id') == get_current_user_id() && $attendant->getIsStaffMemberAssignedToBookingsOnly()) {
			$assistantsIDs[] = $attendant->getId();
		    }
		}

		if ( ! empty( $assistantsIDs ) ) {
                    $meta_query   = $query->get('meta_query') ? $query->get('meta_query') : array();
                    $meta_query[] = array(
                        'key'   => '_sln_booking_services',
                        'value' => implode('|', array_map(function ($v) {
                            return sprintf('"attendant";i:%s;', $v);
                        }, $assistantsIDs)),
                        'compare' => 'REGEXP',
                    );
		    $query->set('meta_query', $meta_query);
		}
	    }
        }
        return $query;
    }
    public function posts_join ( $join, $wp_query ) {
        global $pagenow, $wpdb;
        // I want the filter only when performing a search on edit page of Custom Post Type named "segnalazioni".

        if ( is_admin() && 'edit.php' === $pagenow  && ! empty($_GET['post_type'] )  && $this->getPostType() === $_GET['post_type'] && ! empty( $_GET['s'] ) && ( isset($wp_query->query_vars['post_type'] )&& $wp_query->query_vars['post_type'] === $this->getPostType() ) ) {
            $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' sln_mt ON ' . $wpdb->posts . '.ID = sln_mt.post_id ';
        }
        return $join;
    }

    public function posts_where( $where ) {
        global $pagenow, $wpdb;

        // I want the filter only when performing a search on edit page of Custom Post Type named "segnalazioni".
        if ( is_admin() && 'edit.php' === $pagenow && ! empty($_GET['post_type'] ) && $this->getPostType() === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                " (" . $wpdb->posts . ".post_title LIKE $1) OR (sln_mt.meta_value LIKE $1) ", $where );
        }
        return $where;
    }
    public function posts_distinct( $where ){
        global $pagenow, $wpdb;

        if ( is_admin() && $pagenow=='edit.php' && ! empty($_GET['post_type'] ) && $_GET['post_type']==$this->getPostType() && !empty($_GET['s'])) {
        return "DISTINCT";

        }
        return $where;
    }

    public  function months_dropdown_results($months, $post_type){

        if($post_type !== $this->getPostType() ) return $months;
        global $wpdb;
        $extra_checks = "AND post_status != 'auto-draft'";
            if ( ! isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) {
                $extra_checks .= " AND post_status != 'trash'";
            } elseif ( isset( $_GET['post_status'] ) ) {
                $extra_checks = $wpdb->prepare( ' AND post_status = %s', sanitize_text_field(wp_unslash($_GET['post_status'])) );
            }
                $months = $wpdb->get_results(
                $wpdb->prepare(
                    "
                SELECT DISTINCT YEAR( meta_value ) AS year, MONTH( meta_value) AS month

                FROM $wpdb->postmeta as pt
                LEFT JOIN $wpdb->posts as p
                ON pt.post_id = p.ID
                WHERE pt.meta_key = '_sln_booking_date' AND
                post_type = %s
                $extra_checks
                ORDER BY meta_value DESC
            ", $post_type
                )
            );

            return $months;
    }

    public function post_row_actions($actions, $post) {
        if ($post && $post->post_type === SLN_Plugin::POST_TYPE_BOOKING) {
            unset($actions['inline hide-if-no-js']);
            if(defined('SLN_VERSION_PAY') && SLN_VERSION_PAY){
                if (current_user_can('edit_' . SLN_Plugin::POST_TYPE_BOOKING. 's')) {

                    $actions['clone'] = '<a href="'.$this->getDuplicateActionPostLink($post->ID).'" title="'
                            . esc_attr__("Duplicate this item", 'salon-booking-system')
                            . '">' .  esc_html__('Duplicate', 'salon-booking-system') . '</a>';
                }
            }
        }
        return $actions;
    }

    public function getDuplicateActionPostLink( $id = 0, $context = 'display') {

        $action_name = 'duplicate';
    
        if ( 'display' == $context ) {
            $action = '?post_type='. $this->getpostType().  '&action='.$action_name.'&amp;post='.$id;
        } else {
            $action = '?post_type='. $this->getpostType(). '&action='.$action_name.'&post='.$id;
        }
    
        return wp_nonce_url(admin_url( "post-new.php". $action ), 'sln_duplicate-post_' . $id);
    }

    public function manage_columns($columns)
    {
        $ret = array(
            'cb' => $columns['cb'],
            'ID' => __('Booking ID'),
            'booking_date' => __('Booking Date', 'salon-booking-system'),
            'booking_status' => __('Status', 'salon-booking-system'),
            'myauthor' => __('User name', 'salon-booking-system'),
            'booking_attendant' => __('Attendant', 'salon-booking-system'),
            'booking_duration' => __('Duration', 'salon-booking-system'),
            'booking_price' => __('Booking Price', 'salon-booking-system'),
            'booking_services' => __('Booking Services', 'salon-booking-system'),
            'booking_actions' => __('Actions', 'salon-booking-system'),
        );

        if ($this->getPlugin()->getSettings()->get('sms_remind') || $this->getPlugin()->getSettings()->get('email_remind')) {
            $ret['booking_reminder'] = __('Reminders', 'salon-booking-system');
        }

        return $ret;
    }

    public function manage_column($column, $post_id)
    {
        $obj = $this->getPlugin()->createBooking($post_id);
        if($obj->getStatus() == SLN_Enum_BookingStatus::DRAFT){
            return;
        }

	    $customer = $obj->getCustomer();

        switch ($column) {
            case 'ID' :
                echo edit_post_link($post_id, '<p>', '</p>', $post_id);
                break;
            case 'myauthor':
		echo '<a href="'.esc_url(add_query_arg(array('page' => SLN_Admin_Customers::PAGE, 'id' => $customer ? $customer->getId() : null), admin_url('admin.php'))).'">'.$obj->getDisplayName().'</a>';
                break;
            case 'booking_status' :
                $status = SLN_Enum_BookingStatus::getLabel(get_post_status($post_id));
                $color  = SLN_Enum_BookingStatus::getRealColor(get_post_status($post_id));
                $weight = 'normal';
                if (get_post_status($post_id) == SLN_Enum_BookingStatus::CONFIRMED || get_post_status($post_id) == SLN_Enum_BookingStatus::PAID) $weight = 'bold';
                echo '<div style="width:14px !important; height:14px; border-radius:14px; border:2px solid '.$color.'; float:left; margin-top:2px;"></div> &nbsp;<span style="color:'.$color.'; font-weight:'.$weight.';">' . $status . '</span>';
                break;
            case 'booking_duration':
                $duration = SLN_Func::convertToHoursMins(SLN_Func::getMinutesFromDuration($obj->getDuration()));
                echo $duration;
                break;
            case 'booking_date':
                echo $this->getPlugin()->format()->datetime(
                    new SLN_DateTime(
                        get_post_meta($post_id, '_sln_booking_date', true)
                        .' '.get_post_meta($post_id, '_sln_booking_time', true)
                    )
                );
                break;
            case 'booking_price' :
                echo $this->getPlugin()->format()->money(get_post_meta($post_id, '_sln_booking_amount', true));
                if (get_post_status($post_id) == SLN_Enum_BookingStatus::PAID && $deposit = get_post_meta(
                        $post_id,
                        '_sln_booking_deposit',
                        true
                    )
                ) {
                    echo '(deposit '.$this->getPlugin()->format()->money($deposit).')';
                }
                break;
            case 'booking_services' :
                $name_services = array();
                foreach ($obj->getServices() as $helper) {
                    $name_services[] = $helper->getName();
                }
                echo implode(', ', $name_services);
                break;
            case 'booking_attendant' :
                $theId = $obj->getAttendantsIds(true);
                if (count($theId) > 0):
                    $attendantId = array_values($theId)[0];
                    echo get_the_post_thumbnail($attendantId, array(20, 20), array('valign' => 'middle'));
                    echo ' &nbsp;';
                endif;
                echo $obj->getAttendantsString();
                break;
            case 'booking_review' :
                $comments = get_comments("post_id=$post_id&type=sln_review");
                $comment = isset($comments[0]) ? $comments[0] : null;

                echo '<input type="hidden" name="sln-rating" value="'.$obj->getRating().'">
                        <div class="rating" style="display: none;"></div>';

                if (!empty($comment)) {
                    echo '<a href="'.esc_url(add_query_arg(array('p' => $post_id), admin_url('edit-comments.php'))).'#salon-review"
                            class="overflow-dots">'.$comment->comment_content.'</a>';
                }

                break;
            case 'booking_reminder' :

                $statuses = array(SLN_Enum_BookingStatus::PAID, SLN_Enum_BookingStatus::CONFIRMED, SLN_Enum_BookingStatus::PAY_LATER);

                if (in_array($obj->getStatus(), $statuses)) {

                    echo '<div>';

                    if ($this->getPlugin()->getSettings()->get('email_remind')) {

                        $show = true;
                        $title = '';
                        $class = '';

                        $email_remind = $obj->getMeta('email_remind');

                        if (empty($email_remind)) {

                            $remind_error = $obj->getMeta('email_remind_error');

                            if (empty ($remind_error)) {
                                $interval = $this->getPlugin()->getSettings()->get('email_remind_interval');
                                $datetime = $obj->getStartsAt()->modify('-' . str_replace('+', '', $interval));
	                            $now = new SLN_DateTime();
                                // don't show if time of remind was passed, and we have no info
                                if ($now > $datetime) {
                                    //$show = false;
                                }
                                $title = sprintf(
                                    __('Email will be sent at %s', 'salon-booking-system'),
                                    $this->getPlugin()->format()->date($datetime) . '/' . $this->getPlugin()->format()->time($datetime)
                                );
                                $class = 'sln-booking-reminder-await';
                            } else {
                                $title = __('Email failed', 'salon-booking-system') . ' ' .$remind_error;
                                $class = 'sln-booking-reminder-error';
                            }
                        } else {

                            $email_remind_utc_time = $obj->getMeta('email_remind_utc_time');

                            if ($email_remind_utc_time) {
                                $datetime = (new SLN_DateTime($obj->getMeta('email_remind_utc_time'), new DateTimeZone('UTC')))->setTimezone(SLN_DateTime::getWpTimezone());
                            } else {
                                $interval = $this->getPlugin()->getSettings()->get('email_remind_interval');
                                $datetime = $obj->getStartsAt()->modify('-' . str_replace('+', '', $interval));
                            }
                            $title = sprintf(
                                __('Email correctly sent on %s', 'salon-booking-system'),
                                $this->getPlugin()->format()->date($datetime) . '/' . $this->getPlugin()->format()->time($datetime)
                            );
                            $class = 'sln-booking-reminder-success';
                        }

                        if ($show) {
	                        echo sprintf(
		                        '<span class="sln-booking-reminder-email sln-booking-reminder %s" title="%s"></span>',
		                        $class,
		                        $title
	                        );
                        }
                    }

                    if ($this->getPlugin()->getSettings()->get('sms_remind')) {

                        $title = '';
                        $class = '';
	                    $show = true;

                        $sms_remind = $obj->getMeta('sms_remind');

                        if (empty($sms_remind)) {

                            $remind_error = $obj->getMeta('sms_remind_error');

                            if (empty ($remind_error)) {
                                $interval = $this->getPlugin()->getSettings()->get('sms_remind_interval');
                                $datetime = $obj->getStartsAt()->modify('-' . str_replace('+', '', $interval));
	                            $now = new SLN_DateTime();
	                            // don't show if time of remind was passed, and we have no info
	                            if ($now > $datetime) {
		                           // $show = false;
	                            }
                                $title = sprintf(
                                    __('Sms will be sent at %s', 'salon-booking-system'),
                                    $this->getPlugin()->format()->date($datetime) . '/' . $this->getPlugin()->format()->time($datetime)
                                );
                                $class = 'sln-booking-reminder-await';
                            } else {
                                $title = __('Sms failed', 'salon-booking-system') . ' ' . $remind_error;
                                $class = 'sln-booking-reminder-error';
                            }
                        } else {

                            $sms_remind_utc_time = $obj->getMeta('sms_remind_utc_time');

                            if ($sms_remind_utc_time) {
                                $datetime = (new SLN_DateTime($obj->getMeta('sms_remind_utc_time'), new DateTimeZone('UTC')))->setTimezone(SLN_DateTime::getWpTimezone());
                            } else {
                                $interval = $this->getPlugin()->getSettings()->get('sms_remind_interval');
                                $datetime = $obj->getStartsAt()->modify('-' . str_replace('+', '', $interval));
                            }
                            $title = sprintf(
                                __('Sms correctly sent on %s', 'salon-booking-system'),
                                $this->getPlugin()->format()->date($datetime) . '/' . $this->getPlugin()->format()->time($datetime)
                            );
                            $class = 'sln-booking-reminder-success';
                        }

                        if ($show) {
	                        echo sprintf(
		                        '<span class="sln-booking-reminder-sms sln-booking-reminder %s" title="%s"></span>',
		                        $class,
		                        $title
	                        );
                        }
                    }

                    echo '</div>';

                }

                break;

                case 'booking_actions' :
                    if ( $this->getPlugin()->getSettings()->get('confirmation') && $obj->getStatus(
) == SLN_Enum_BookingStatus::PENDING) {
                        $title = !defined("SLN_VERSION_PAY") ? __('Switch to PRO to unlock the "Quick approval"', 'salon-booking-system') : '';
                        echo '<div class="sln-booking-confirmation '. ( !defined("SLN_VERSION_PAY") ? 'sln-booking-confirmation-disabled' : '') .'">';
                            echo '<div class="sln-booking-confirmation-alert-loading"></div>';
                            echo '<div class="sln-booking-confirmation-tooltip"><a href="https://www.salonbookingsystem.com/homepage/plugin-pricing/?utm_source=quick_confirm_booking&utm_medium=free-edition-back-end&utm_campaign=unlock_feature&utm_id=GOPRO" target="_blank">'. $title .'</a></div>';
                            echo '<div class="sln-booking-confirmation-success" data-status="' . SLN_Enum_BookingStatus::CONFIRMED . '" data-booking-id="' . $obj->getId() . '" title="' . ( $title ? '' : __('Accept', 'salon-booking-system') ) . '" data-class="success"></div>';
                            echo '<div class="sln-booking-confirmation-error" data-status="' . SLN_Enum_BookingStatus::CANCELED . '" data-booking-id="' . $obj->getId() . '" title="' . ( $title ? '' : __('Refuse', 'salon-booking-system') ) . '" data-class="danger"></div>';
                        echo '</div>';
				    }

                break;
        }
    }

    public function enter_title_here($title, $post)
    {
        if ($post && $this->getPostType() === $post->post_type) {
            $title = __('Enter booking name', 'salon-booking-system');
        }

        return $title;
    }

    public function updated_messages($messages)
    {
        global $post, $post_ID;

        $messages[$this->getPostType()] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(
                __('Booking updated.', 'salon-booking-system')
            ),
            2 => __('Booking confirmed', 'salon-booking-system'),
            3 => __('Booking cancelled', 'salon-booking-system'),
            4 => __('Booking updated.', 'salon-booking-system'),
            5 => isset($_GET['revision']) ? sprintf(
                __('Booking restored to revision from %s', 'salon-booking-system'),
                wp_post_revision_title((int)$_GET['revision'], false)
            ) : false,
            6 => sprintf(
                __('Booking published.', 'salon-booking-system')
            ),
            7 => __('Booking saved.', 'salon-booking-system'),
            8 => sprintf(
                __('Booking submitted.', 'salon-booking-system')
            ),
            9 => sprintf(
                __(
                    'Booking scheduled for: <strong>%1$s</strong>.',
                    'salon-booking-system'
                ),
                SLN_TimeFunc::translateDate(__('M j, Y @ G:i', 'salon-booking-system'), SLN_TimeFunc::getPostTimestamp($post))
            ),
            10 => sprintf(
                __('Booking draft updated.', 'salon-booking-system')
            ),
        );


        return $messages;
    }

    protected function getPostTypeArgs()
    {
        return array(
            'description' => __('This is where bookings are stored.', 'salon-booking-system'),
            'public' => true,
            'show_ui' => true,
            'map_meta_cap' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_in_menu' => 'salon',
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'rewrite' => false,
            'query_var' => false,
            'supports' => array('title', 'comments', 'custom-fields'),
            'has_archive' => false,
            'rewrite' => false,
            'supports' => array(
                'revisions',
            ),
            'labels' => array(
                'name' => __('Bookings', 'salon-booking-system'),
                'singular_name' => __('Booking', 'salon-booking-system'),
                'menu_name' => __('Salon', 'salon-booking-system'),
                'name_admin_bar' => __('Salon Booking', 'salon-booking-system'),
                'all_items' => __('Bookings', 'salon-booking-system'),
                'add_new' => __('Add Booking', 'salon-booking-system'),
                'add_new_item' => __('Add New Booking', 'salon-booking-system'),
                'edit_item' => __('Edit Booking', 'salon-booking-system'),
                'new_item' => __('New Booking', 'salon-booking-system'),
                'view_item' => __('View Booking', 'salon-booking-system'),
                'search_items' => __('Search Bookings', 'salon-booking-system'),
                'not_found' => __('No bookings found', 'salon-booking-system'),
                'not_found_in_trash' => __('No bookings found in trash', 'salon-booking-system'),
                'archive_title' => __('Booking Archive', 'salon-booking-system'),
            ),
            'capability_type' => array($this->getPostType(), $this->getPostType().'s'),
            'map_meta_cap' => true,
            'capabilities' => array(
                'create_posts' => 'create_' . $this->getPostType().'s',
            ),
        );
    }

    private function registerPostStatus()
    {
        foreach (SLN_Enum_BookingStatus::toArray() as $k => $v) {
            register_post_status(
                $k,
                array(
                    'label' => $v,
                    'public' => true,
                    'exclude_from_search' => false,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop(
                        $v.' <span class="count">(%s)</span>',
                        $v.' <span class="count">(%s)</span>'
                    ),
                )
            );
        }
        add_action('transition_post_status', array($this, 'transitionPostStatus'), 10, 3);
    }

    public function transitionPostStatus($new_status, $old_status, $post)
    {
        if (
	    $post
	    && $post->post_type == SLN_Plugin::POST_TYPE_BOOKING
            && $old_status != $new_status
        ) {
            $p = $this->getPlugin();
            $booking = $p->createBooking($post);
            $p->messages()->sendByStatus($booking, $new_status);
        }
    }


    public function bulkAdminFooterNew()
    {
        $this->bulkAdminFooter(true);
    }

    public function bulkAdminFooterEdit()
    {
        $this->bulkAdminFooter(false);
    }

    public function bulkAdminFooter($isNew = false)
    {
        global $post;
        if ($post && $post->post_type == SLN_Plugin::POST_TYPE_BOOKING) {
            ?>
            <script type="text/javascript">
                jQuery(function ($) {
                    $('#save-post').attr('value', '<?php echo __(
                        $isNew ? "Add booking" : 'Update booking',
                        'salon-booking-system'
                    ) ?>').addClass('sln-btn sln-btn--main');
                    $('#major-publishing-actions').css('display', 'none');
                    $('#submitdiv h3 span').text('<?php echo __('Booking', 'salon-booking-system') ?>');
                    <?php
                    foreach (SLN_Enum_BookingStatus::toArray() as $k => $v) {
                    $complete = '';
                    $label = '';
                    if ($post->post_status == $k) {
                        $complete = ' selected=\"selected\"';
                        $label = '<span id=\"post-status-display\">'.$v.'</span>';
                    }
                    ?>
                    $("select#post_status").append("<option value=\"<?php echo $k ?>\" <?php echo $complete ?>><?php echo $v ?></option>");
                    $(".misc-pub-section label").append("<?php echo $label ?>");
                    <?php
                    }
                    ?>
                });
            </script>
            <?php
        }
    }

    public function bulkPostStates($post_states, $post)
    {
        global $post;
        $arg = get_query_var('post_status');
        if ($post && $post->post_type == SLN_Plugin::POST_TYPE_BOOKING) {
            foreach (SLN_Enum_BookingStatus::toArray() as $k => $v) {

                if ($arg != $k) {
                    if ($post->post_status == $k) {
                        return array($v);
                    }
                }
            }
        }

        return $post_states;
    }

    public function posttype_admin_css()
    {
        global $post_type;
        if ($post_type == SLN_Plugin::POST_TYPE_BOOKING) {
            echo $this->getPlugin()->loadView('metabox/_booking_head');
        }
    }

    /**
     * @param WP_Query $query
     */
    public function parse_query($query) {
        global $pagenow;

        if (isset($_GET['post_type']) && $_GET['post_type'] === $this->getPostType() && is_admin() && $pagenow=='edit.php' && $query->get('post_type') === $this->getPostType()) {
            $meta_queries = array();
            if (isset($_GET['m']) && !empty($_GET['m'])) {
                $m =  sanitize_text_field(wp_unslash($_GET['m']));
                $y =  substr ( $m , 0 ,4 );
                $m =  substr ( $m , 4, 2 );
                $meta_queries[] = array(
                    'key'     => '_sln_booking_date',
                    'value'   => array($y."-".$m."-01",$y."-".$m."-31"),
                    'compare' => 'BETWEEN',
                );
            }

	    if ( ! empty( $_GET['service'] ) ) {
                $service = sanitize_text_field(wp_unslash($_GET['service']));
                $meta_queries[] = array(
                    'key'     => '_sln_booking_services',
                    'value'   => "\"service\";i:{$service};",
                    'compare' => 'LIKE',
                );
            }

            if (isset($_GET['attendant']) && !empty($_GET['attendant'])) {
                $attendant = sanitize_text_field(wp_unslash($_GET['attendant']));
                $meta_queries[] = array(
                    'key'     => '_sln_booking_services',
                    'value'   => "\"attendant\";i:{$_GET['attendant']};",
                    'compare' => 'LIKE',
                );
            }

            if (isset($_GET['username']) && !empty($_GET['username'])) {
                $username_parts = explode('|', sanitize_text_field(wp_unslash($_GET['username'])));
                if (!empty($username_parts[0])) {
                    $meta_queries[] = array(
                        'key'   => '_sln_booking_firstname',
                        'value' => $username_parts[0],
                    );
                }
                if (!empty($username_parts[1])) {
                    $meta_queries[] = array(
                        'key'   => '_sln_booking_lastname',
                        'value' => $username_parts[1],
                    );
                }
            }

            if (!empty($meta_queries)) {
                $meta_queries['relation'] = 'AND';

                $meta_query = $query->get('meta_query');

                $meta_query = array_merge(!empty($meta_query) ? $meta_query : array(), $meta_queries);
                $query->set('meta_query', $meta_query);
            }
        }
    }

    public function restrict_manage_posts($post_type, $which = null) {
        global $wpdb;
        if ($post_type === $this->getPostType() && $which === 'top') {
            $statuses = SLN_Enum_BookingStatus::toArray();

            $rows  = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='_sln_booking_firstname' OR meta_key='_sln_booking_lastname'");

            $users = array();
            foreach($rows as $row) {
                $users[$row->post_id][$row->meta_key] = $row->meta_value;
            }

            $users_name = array();
            foreach($users as $user) {
                if (!isset($user['_sln_booking_firstname'])) {
                    $user['_sln_booking_firstname'] = '';
                }
                if (!isset($user['_sln_booking_lastname'])) {
                    $user['_sln_booking_lastname'] = '';
                }
                $users_name[$user['_sln_booking_firstname'].'|'.$user['_sln_booking_lastname']] = $user['_sln_booking_firstname'].' '.$user['_sln_booking_lastname'];
            }

            $repo       = $this->getPlugin()->getRepository(SLN_Plugin::POST_TYPE_SERVICE);
            $services   = $repo->getAll();

            $repo       = $this->getPlugin()->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT);
            $attendants = $repo->getAll();
            ?>
            <?php $current = isset($_GET['post_status']) ? sanitize_text_field(wp_unslash($_GET['post_status'])) : ''; ?>
	    <?php if ($current !== 'trash'): ?>
		<select name="post_status" id="filter-by-post_status">
		    <option value=""><?php _e('All Statuses', 'salon-booking-system') ?></option>
		    <?php foreach($statuses as $k => $v): ?>
			<option value="<?php echo $k; ?>" <?php echo ($current === $k ? 'selected' : ''); ?>><?php echo $v; ?></option>
		    <?php endforeach ?>
		</select>
	    <?php endif ?>

            <?php $current = isset($_GET['username']) ? sanitize_text_field(wp_unslash($_GET['username'])) : ''; ?>
            <select name="username" id="filter-by-username">
                <option value=""><?php _e('All users name', 'salon-booking-system') ?></option>
                <?php foreach($users_name as $k => $v): ?>
                    <option value="<?php echo $k; ?>" <?php echo ($current === $k ? 'selected' : ''); ?>><?php echo $v; ?></option>
                <?php endforeach ?>
            </select>

            <?php $current = isset($_GET['service']) ? (int) $_GET['service'] : ''; ?>
            <select name="service" id="filter-by-service">
                <option value=""><?php _e('All services', 'salon-booking-system') ?></option>
                <?php foreach($services as $v): ?>
                    <option value="<?php echo $v->getId(); ?>" <?php echo ($current === $v->getId() ? 'selected' : ''); ?>><?php echo $v->getTitle(); ?></option>
                <?php endforeach ?>
            </select>

            <?php $current = isset($_GET['attendant']) ? (int) $_GET['attendant'] : ''; ?>
            <select name="attendant" id="filter-by-attendant">
                <option value=""><?php _e('All attendants', 'salon-booking-system') ?></option>
                <?php foreach($attendants as $v): ?>
                    <option value="<?php echo $v->getId(); ?>" <?php echo ($current === $v->getId() ? 'selected' : ''); ?>><?php echo $v->getTitle(); ?></option>
                <?php endforeach ?>
            </select>
            <?php
        }
    }

}
