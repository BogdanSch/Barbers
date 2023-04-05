<?php
$addAjax = apply_filters('sln.template.calendar.ajaxUrl', '');
$ai = $plugin->getSettings()->getAvailabilityItems();
list($timestart, $timeend) = $ai->getTimeMinMax();
$timesplit = $plugin->getSettings()->getInterval();
$holidays_rules = apply_filters('sln.get-day-holidays-rules', $plugin->getSettings()->getDailyHolidayItems());

$holidays_assistants_rules  = array();
$assistants                 = $plugin->getRepository(SLN_Plugin::POST_TYPE_ATTENDANT)->getAll();
foreach ($assistants as $att) {
    $holidays_assistants_rules[$att->getId()] = $att->getMeta('holidays_daily')?:array();
}
$holidays_assistants_rules = apply_filters('sln.get-day-holidays-assistants-rules', $holidays_assistants_rules, $assistants);
$day_calendar_holydays_ajax_data = apply_filters('sln.get-day-calendar-holidays-ajax-data', array());
$day_calendar_columns = $plugin->getSettings()->get('parallels_hour') * 2 + 1;
$replace_booking_modal_with_popup = $plugin->getSettings()->get('replace_booking_modal_with_popup');
?>
<script type="text/javascript">
    var salon;
    var calendar_translations = {
        'Go to daily view': '<?php _e('Go to daily view', 'salon-booking-system')?>'
    };
    var salon_default_duration = <?php echo $timesplit; ?>;
    var daily_rules = JSON.parse('<?php echo json_encode($holidays_rules); ?>');
    var daily_assistants_rules = JSON.parse('<?php echo json_encode($holidays_assistants_rules); ?>');
    var holidays_rules_locale = {
        'block':'<?php _e('Block', 'salon-booking-system');?>',
        'block_confirm':'<?php _e('CONFIRM', 'salon-booking-system');?>',
        'unblock':'<?php _e('Unlock', 'salon-booking-system');?>',
        'unblock_these_rows':'<?php _e('UNLOCK', 'salon-booking-system');?>',
    }
    var sln_search_translation = {
        'tot':'<?php _e('Tot.', 'salon-booking-system');?>',
        'edit':'<?php _e('Edit', 'salon-booking-system');?>',
        'cancel':'<?php _e('Cancel', 'salon-booking-system');?>',
        'no_results':'<?php _e('No results', 'salon-booking-system');?>'
    }
    var calendar_locale = {
        'add_event':'<?php _e('Add book', 'salon-booking-system');?>',
    }

    var dayCalendarHolydaysAjaxData = JSON.parse('<?php echo json_encode($day_calendar_holydays_ajax_data); ?>');

    var dayCalendarColumns = '<?php echo $day_calendar_columns ?>';

<?php $today = new DateTime()?>
jQuery(function($){
    sln_initSalonCalendar(
        $,
        salon.ajax_url+"&action=salon&method=calendar&security="+salon.ajax_nonce+'<?php echo $addAjax ?>',
//        '<?php echo SLN_PLUGIN_URL ?>/js/events.json.php',
        '<?php echo $today->format('Y-m-d') ?>',
        '<?php echo SLN_PLUGIN_URL ?>/views/js/calendar/',
        '<?php echo $plugin->getSettings()->get('calendar_view') ?: 'month' ?>',
        '<?php echo $plugin->getSettings()->get('week_start') ?: 0 ?>'
    );
});

var replaceBookingModalWithPopup = +'<?php echo $replace_booking_modal_with_popup ?>';

</script>
<style>
.day-calbar,
.week-calbar{
    display: block;
    margin: 8px 15px 8px 15px;
    height: 8px;
    width: 100%;
    background-color: #dfdfdf;
}
.week-calbar{
    margin-top: -8px;
}
.month-calbar{
    display: block;
    height: 8px;
    width: 100%;
    background-color: #dfdfdf;
}
.calbar .busy{
    display: block;
    background-color: red;
    height: 8px;
    float: left;
}
.calbar .free{
    display: block;
    height: 8px;
    float: left;
    background-color: green;
}
.calbar-tooltip{
    background-color: #c7dff3;
    display: inline-block;
    width: 340px;
    height: 50px;
    padding: 5px;
    margin: -20px 0 -10px -80px;
}
.calbar-tooltip span{
    float: left;
    display: block;
    width: 33%;
    color: #666;
}
.calbar-tooltip strong{
    font-size: 16px;
    color: #0C6EB6;
    display: block;
    clear: both;
}
#cal-day-box .day-event-panel-border{
    z-index: 610;
    position: absolute;
    height: inherit;
    width: 1px;
    background-color: #d4d4d4;
    top: -10px;
    left: 81px;
}
#cal-day-box #cal-day-panel-hour{
    z-index: 997 !important;
}
#cal-day-box .day-event{
    width: 199px !important;
    max-width: 199px !important;
    left: 82px;
}
#cal-day-box .cal-day-assistants{
    margin: 0 0 0 280px;
    width: 91.2%;
}
#cal-day-box .cal-day-assistant{
    display: inline-block;
    text-align: center;
    width: 200px !important;
    margin-right: -4px;
    font-size: 1.2em;
    font-weight: 600
}
#cal-day-box .day-highlight{
    border-left: none !important;
    cursor: pointer;
}
#cal-day-box .day-highlight:hover{
    text-decoration: underline;
}

.cal-day-hour-part .block_date,.cal-day-hour-part [data-action=add-event-by-date] {
    width: 5%;
    min-width: 5% !important;
    padding: 0 0.3rem;
    height: 28px;
    display: none;
}

.col-xs-12.col-md-6.mt-md-5.sln-box-title.current-view--title{margin-top: 60px; font-weight: 600; text-transform: uppercase;}

@media only screen and (min-width: 1200px) {
    .cal-day-hour-part [data-action=add-event-by-date] {
        width: 7%;
    }
}
.cal-day-hour-part{
    position: relative;
    z-index: 998;
}
.cal-day-hour-part.active .block_date,.cal-day-hour-part.active [data-action=add-event-by-date] {
    display: inline-block;
    z-index: 999;
}
.cal-day-hour-part.selected [data-action=add-event-by-date]{
    display: none;
}
.cal-day-hour-part.active .block_date{
    transform: translateY(-50%);
}
#cal-day-box .cal-day-assistants{
    width: auto;
}

</style>
<div class="sln-bootstrap sln-calendar-plugin-update-notice--wrapper">
     <?php if (!defined("SLN_VERSION_PAY")): ?>
<div class="row">
    <div class="col-xs-12 sln-notice__wrapper">
        <div class="sln-notice sln-notice--bold sln-notice--subscription-free-version">
            <div class="sln-notice--bold__text">
                <h2><?php _e('<strong>Join a community of 2,000 + priority customers for a special price</strong>', 'salon-booking-system')?></h2>
             <p><?php _e('Users of the free edition can get a special discount and unlock more than 20 PRO features, 30 add-ons and access to the mobile App.
', 'salon-booking-system') ?></p>
               <p><?php _e('<strong>It’s a limited time promo, don’t miss it.</strong>', 'salon-booking-system')?></p>
            </div>
            <a href="https://www.salonbookingsystem.com/checkout?edd_action=add_to_cart&download_id=64398&edd_options%5Bprice_id%5D=2&discount=GOPRO25" target="_blank" class="sln-notice--plugin_update__action"><?php _e('Get your discount', 'salon-booking-system')?></a>
        </div>
    </div>
</div>
    <?php else: ?>
    <?php
global $sln_license;
if ($sln_license) {
    $sln_license->checkSubscription();
    $subscriptions_data = $sln_license->get('subscriptions_data');
}
$subscription = isset($subscriptions_data->subscriptions[0]) ? $subscriptions_data->subscriptions[0] : null;
$expire_days = $subscription ? ceil((strtotime($subscription->info->expiration) - current_time('timestamp')) / (24 * 3600)) : 0;
$expire = sprintf(_n('%s day', '%s days', $expire_days, 'salon-booking-system'), $expire_days);
?>
    <?php if ($sln_license && !$sln_license->get('license_data') && !in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ): ?>
        <?php
$page_slug = $sln_license->get('slug') . '-license';
$license_url = admin_url('/plugins.php?page=' . $page_slug);
?>
        <div class="row">
            <div class="col-xs-12 sln-notice__wrapper">
                <div class="sln-notice sln-notice--bold sln-notice--subscription-expired">
                    <div class="sln-notice--bold__text">
                        <h2><?php _e('<strong>Attention:</strong> Please activate your license first', 'salon-booking-system')?></h2>
                    </div>
                    <a href="<?php echo $license_url ?>" target="_blank" class="sln-notice--plugin_update__action"><?php _e('Activate your license', 'salon-booking-system')?></a>
                </div>
            </div>
        </div>
    <?php endif;?>
    <?php if ($subscription && !in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles)): ?>
        <?php if ($subscription->info->status === 'cancelled'): ?>
        <div class="row">
            <div class="col-xs-12 sln-notice__wrapper">
                <div class="sln-notice sln-notice--bold sln-notice--subscription-cancelled">
                    <div class="sln-notice--bold__text">
                        <h2><?php _e('<strong>Your subscription has been cancelled!</strong>', 'salon-booking-system')?></h2>
                     <p><?php echo sprintf(__('Your license will expire in %s, then you need to purchase a new one at its full price to continue using our services.', 'salon-booking-system'), $expire) ?></p>
                       <p><?php _e('<strong>Renew it before the expiration and get a discounted price.</strong>', 'salon-booking-system')?></p>
                    </div>
                    <a href="https://www.salonbookingsystem.com/homepage/plugin-pricing/?utm_source=plugin-back-end_pro&utm_medium=license-status-notice&utm_campaign=renew-license&utm_id=renew-license" target="_blank" class="sln-notice--plugin_update__action"><?php _e('Renew for 15% off', 'salon-booking-system')?></a>
                </div>
            </div>
        </div>
        <?php elseif ($subscription->info->status === 'active'): ?>
        <div class="row">
            <div class="col-xs-12 sln-notice__wrapper">
                <div class="sln-notice sln-notice--bold sln-notice--subscription-active">
                    <div class="sln-notice--bold__text">
                        <h2><?php _e('<strong>Your subscription is active</strong>', 'salon-booking-system')?></h2>
                     <p><?php echo sprintf(__('Your license will expire in %s, then will be automatically renewed.', 'salon-booking-system'), $expire) ?></p>
                       <p><?php _e('<strong>If you are happy with us, please submit a positive review.</strong>', 'salon-booking-system')?></p>
                    </div>
                    <a href="https://reviews.capterra.com/new/166320?utm_source=vp&utm_medium=none&utm_campaign=vendor_request_paid" target="_blank" class="sln-notice--plugin_update__action"><?php _e('Leave a review', 'salon-booking-system')?></a>
                </div>
            </div>
        </div>
        <?php elseif ($subscription->info->status === 'expired'): ?>
        <div class="row">
            <div class="col-xs-12 sln-notice__wrapper">
                <div class="sln-notice sln-notice--bold sln-notice--subscription-expired">
                    <div class="sln-notice--bold__text">
                        <h2><?php _e('<strong>Your subscription is expired!</strong>', 'salon-booking-system')?></h2>
                     <p><?php _e('<strong>Attention:</strong> your subscription to <strong>Salon Booking System “Business Plan”</strong> has been cancelled and is expired', 'salon-booking-system')?></p>
                       <p><?php _e('<strong>Renew it now and get a discounted price.</strong>', 'salon-booking-system')?></p>
                    </div>
                    <a href="https://www.salonbookingsystem.com/checkout?edd_action=add_to_cart&download_id=64398&edd_options%5Bprice_id%5D=2&discount=GETBACK30&utm_source=plugin-back-end_pro&utm_medium=license-status-notice&utm_campaign=renew-license&utm_id=renew-expired-license" target="_blank" class="sln-notice--plugin_update__action"><?php _e('Renew for 30% off', 'salon-booking-system')?></a>
                </div>
            </div>
        </div>
        <?php endif;?>
    <?php endif;?>
    <?php endif;?>
</div>
<div class="clearfix"></div>
<div class="container-fluid sln-calendar--wrapper sln-calendar--wrapper--loading">
<div class="sln-calendar--wrapper--sub" style="opacity: 0;">

<div class="row">
    <div class="col-xs-12 col-md-6 col-md-push-9 btn-group">
        <?php include 'help.php'?>
    </div>

          <?php do_action('sln.template.calendar.navtabwrapper')?>
</div>
<div class="row" style="display: flex">
    <div class="col-xs-12 <?php echo !defined("SLN_VERSION_PAY") ? 'col-md-6' : '' ?>">
    <div class="row">
        <div class="col-xs-12 btn-group nav-tab-wrapper sln-nav-tab-wrapper">
        <div class="sln-btn sln-btn--borderonly sln-btn--large" data-calendar-view="day">
        <button class="" data-calendar-view="day"><?php _e('Day', 'salon-booking-system')?></button>
        </div>
        <div class="sln-btn sln-btn--borderonly sln-btn--large" data-calendar-view="week">
        <button class="" data-calendar-view="week"><?php _e('Week', 'salon-booking-system')?></button>
        </div>
        <div class="sln-btn sln-btn--borderonly sln-btn--large" data-calendar-view="month">
        <button class=" active" data-calendar-view="month"><?php _e('Month', 'salon-booking-system')?></button>
        </div>
        <div class="sln-btn sln-btn--borderonly sln-btn--large" data-calendar-view="year">
        <button class="" data-calendar-view="year"><?php _e('Year', 'salon-booking-system')?></button>
        </div>
        </div>
    </div>
    </div>
    </div>
    <div class="row">
    <?php if (!defined("SLN_VERSION_PAY") && isset($_COOKIE['sln-notice__dismiss']) && $_COOKIE['sln-notice__dismiss']): ?>
    <div class="col-xs-12 sln-notice__wrapper">
        <div class="sln-notice sln-notice--review">
                <h2><?php _e('Are you happy with us?', 'salon-booking-system') ?> <?php _e('Share your love for <strong>Salon Booking System</strong> leaving a positive review.', 'salon-booking-system') ?>
                    <?php _e("Let's grow our community.", 'salon-booking-system') ?>
                    <a href="https://wordpress.org/support/plugin/salon-booking-system/reviews/?filter=5#new-post" target="_blank" class="sln-notice--action">
                        <?php _e('Submit a review', 'salon-booking-system')?>
                    </a>
                </h2>
                <button type="button" class="sln-notice__dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    </div>
    <?php endif;?>
    <?php if (!defined("SLN_VERSION_PAY")): ?>
    <div class="col-xs-12 sln-notice__wrapper">
        <div class="sln-notice sln-notice--useapp">
    <h2><?php _e('Install our app, is much<a target="_blank" href="https://icons8.com">Icons8</a> more easy to use on a mobile device, and it can be used by your workers too.', 'salon-booking-system')?> <a href="https://www.salonbookingsystem.com/salon-booking-system-mobile-app/?utm_source=Free-edition&utm_medium=link-back-end-calendar&utm_campaign=push-to-pro&utm_content=use%20app" target="_blank"  class="sln-notice--action"><?php _e('Read more..', 'salon-booking-system')?></a></h2>
                <button type="button" class="sln-notice__dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    </div>
    <?php endif;?>
</div>
<div class="row">
    <div class="col-xs-12 col-md-6 mt-md-5 sln-box-title current-view--title"></div>
    <?php if($plugin->getSettings()->isAttendantsEnabled()): ?>
        <div class="col-xs-12 col-md-6 form-group sln-switch cal-day-filter">
        <div class="pull-right">
            <span class="sln-fake-label"><?php _e('Assistants view', 'salon-booking-system')?></span>
            <?php
    SLN_Form::fieldCheckbox(
        "sln-calendar-assistants-mode-switch",
        ($checked = get_user_meta(get_current_user_id(), '_assistants_mode', true)) !== '' ? $checked && $checked != 'false' : false
    )
    ?>
            <label for="sln-calendar-assistants-mode-switch" class="sln-switch-btn" data-on="On" data-off="Off"></label>
        </div>
        </div>
    <?php endif; ?>
</div>

<div class="row sln-calendar-view sln-box">
    <div class="col-xs-12 form-inline">
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-sm-push-6">
                <div class="sln-calendar-viewnav btn-group">
                    <div class="sln-btn sln-btn--light sln-btn--large  sln-btn--icon sln-btn--icon--left sln-icon--arrow--left" data-calendar-view="day">
                        <button class="f-row" data-calendar-nav="prev"><?php _e('Previous', 'salon-booking-system')?></button>
                    </div>
                    <div class="sln-btn sln-btn--light sln-btn--large" data-calendar-view="day">
                        <button class="f-row" data-calendar-nav="today"><?php _e('Today', 'salon-booking-system')?></button>
                    </div>
                    <div class="sln-btn sln-btn--light sln-btn--large  sln-btn--icon sln-icon--arrow--right" data-calendar-view="day">
                        <button class="f-row f-row--end" data-calendar-nav="next"><?php _e('Next', 'salon-booking-system')?></button>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-lg-4 col-md-6 col-sm-pull-6">
                <div class="cal-day-search cal-day-filter">
                    <div class="sln-calendar-booking-search-wrapper"><div class="sln-calendar-booking-search-input-wrapper"><?php
SLN_Form::fieldText(
    "sln-calendar-booking-search", false, ['attrs' => [
        'size' => 32,
        'placeholder' => __("Start typing customer name or booking ID", 'salon-booking-system'),
    ],
    ]
)
?></div>
                    <div class="sln-calendar-booking-search-icon">

                    </div>
                    </div>
                    <div id="search-results-list" class="sln-calendar-search-results-list"></div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-2 col-sm-pull-5 col-lg-pull-4">
                <div class="cal-day-filter cal-day-pagination" style="display: none"></div>
            </div>
        </div>
    </div>

        <div class="clearfix"></div>
        <div id="calendar" data-timestart="<?php echo $timestart ?>" data-timeend="<?php echo $timeend ?>" data-timesplit="<?php echo $timesplit ?>"></div>
    <div class="clearfix"></div>

<!-- row sln-calendar-wrapper // END -->
</div>

<div class="row">
    <div class="form-group col-xs-12 sln-free-locked-slots-block">
        <button class="sln-btn sln-btn--main sln-btn--big sln-free-locked-slots sln-icon--unlock sln-btn--icon">
            <?php _e('Free locked slots', 'salon-booking-system')?>
        </button>
    </div>
</div>

<div id="sln-booking-editor-modal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
               <div class="sln-booking-editor--wrapper">
                    <div class="sln-booking-editor--wrapper--sub" style="opacity: 0">
                        <iframe name="booking_editor" class="booking-editor" width="100%" height="600px" frameborder="0"
                                data-src-template-edit-booking="<?php echo admin_url('/post.php?post=%id&action=edit&mode=sln_editor') ?>"
                                data-src-template-new-booking="<?php echo admin_url('/post-new.php?post_type=sln_booking&date=%date&time=%time&mode=sln_editor') ?>"
                                data-src-template-duplicate-booking="<?php echo admin_url('/post-new.php?post_type=sln_booking&action=duplicate&post=%id&mode=sln_editor') ?>"></iframe>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="booking-last-edit-div pull-left-"></div>
                <div class="pull-right- modal-footer__actions">
                    <button type="button" class="sln-btn sln-btn--nu sln-btn--nu--highemph sln-btn--big" aria-hidden="true" data-action="save-edited-booking"><?php _e('Save', 'salon-booking-system')?></button>
                    <div class="sln-duplicate-booking <?php echo !defined("SLN_VERSION_PAY")  ? 'sln-duplicate-booking--disabled' : '' ?>">
                        <span class="sln-booking-pro-feature-tooltip">
                        <a href="https://www.salonbookingsystem.com/homepage/plugin-pricing/?utm_source=default_status&utm_medium=free-edition-back-end&utm_campaign=unlock_feature&utm_id=GOPRO" target="_blank">
                            <?php echo __('Switch to PRO to unlock this feature', 'salon-booking-system') ?>
                        </a>
                        </span>
                        <button type="button" class="sln-btn sln-btn--nu sln-btn--nu--lowhemph sln-btn--big" aria-hidden="true" data-action="duplicate-edited-booking"><?php _e('Duplicate', 'salon-booking-system')?></button>
                    </div>
                    <button type="button" class="sln-btn sln-btn--nu sln-btn--nu--lowhemph sln-btn--big" aria-hidden="true" data-action="delete-edited-booking"><?php _e('Delete', 'salon-booking-system')?></button>
                    <button type="button" class="sln-btn sln-btn--nu sln-btn--nu--medhemph sln-btn--big" data-dismiss="modal" aria-hidden="true"><?php _e('Close', 'salon-booking-system')?></button>
                </div>
                <div class="modal-footer__flyingactions">
        <?php
        if (!defined("SLN_VERSION_PAY")) {
            $tellafriendurl = "https://www.salonbookingsystem.com/refer-a-friend/?utm_source=plugin-back-end_free&utm_medium=refer-a-friend-link&utm_campaign=refer_a_fiend&utm_id=refer-a-friend";
        } else {
            $tellafriendurl = "https://www.salonbookingsystem.com/refer-a-friend/?utm_source=plugin-back-end_pro&utm_medium=refer-a-friend-link&utm_campaign=refer_a_fiend&utm_id=refer-a-friend";
        }
        ?>
                <?php if ( ! in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ): ?>
                    <a class="sln-btn sln-btn--inline--icon" href="<?php echo $tellafriendurl; ?>" target="_blank"><span><?php _e('Refer a friend and get a 30% discount', 'salon-booking-system')?></span></a>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!get_option('sln_calendar_page_show')): ?>
    <div id="sln-calendar-modal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="close">&times;</div>
        <div class="modal-body">
            <iframe width="100%" height="500px" src="https://www.youtube.com/embed/MGW0hSZrV5c" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        </div>
    </div>
    </div>
    <script>
    jQuery(function() {
        setTimeout(function () {
        jQuery('#sln-calendar-modal').modal();
        }, 0);
        jQuery('#sln-calendar-modal .close').on('click', function () {
        jQuery('#sln-calendar-modal').modal('hide');
        });
        jQuery('#sln-calendar-modal').on('hidden.bs.modal', function () {
            callPlayer('sln-calendar-modal-player', 'stopVideo');
        });
    });
    /**

    * @author       Rob W <gwnRob@gmail.com>

    * @website      https://stackoverflow.com/a/7513356/938089

    * @version      20190409

    * @description  Executes function on a framed YouTube video (see website link)

    *               For a full list of possible functions, see:

    *               https://developers.google.com/youtube/js_api_reference

    * @param String frame_id The id of (the div containing) the frame

    * @param String func     Desired function to call, eg. "playVideo"

    *        (Function)      Function to call when the player is ready.

    * @param Array  args     (optional) List of arguments to pass to function func*/

    function callPlayer(frame_id, func, args) {

        if (window.jQuery && frame_id instanceof jQuery) frame_id = frame_id.get(0).id;
    var iframe = document.getElementById(frame_id);
    if (iframe && iframe.tagName.toUpperCase() != 'IFRAME') {
        iframe = iframe.getElementsByTagName('iframe')[0];
    }

    // When the player is not ready yet, add the event to a queue
    // Each frame_id is associated with an own queue.
    // Each queue has three possible states:
    //  undefined = uninitialised / array = queue / .ready=true = ready
    if (!callPlayer.queue) callPlayer.queue = {};
    var queue = callPlayer.queue[frame_id],
        domReady = document.readyState == 'complete';

    if (domReady && !iframe) {
        // DOM is ready and iframe does not exist. Log a message
        window.console && console.log('callPlayer: Frame not found; id=' + frame_id);
        if (queue) clearInterval(queue.poller);
    } else if (func === 'listening') {
        // Sending the "listener" message to the frame, to request status updates
        if (iframe && iframe.contentWindow) {
            func = '{"event":"listening","id":' + JSON.stringify(''+frame_id) + '}';
            iframe.contentWindow.postMessage(func, '*');
        }
    } else if ((!queue || !queue.ready) && (
               !domReady ||
               iframe && !iframe.contentWindow ||
               typeof func === 'function')) {
        if (!queue) queue = callPlayer.queue[frame_id] = [];
        queue.push([func, args]);
        if (!('poller' in queue)) {
            // keep polling until the document and frame is ready
            queue.poller = setInterval(function() {
                callPlayer(frame_id, 'listening');
            }, 250);
            // Add a global "message" event listener, to catch status updates:
            messageEvent(1, function runOnceReady(e) {
                if (!iframe) {
                    iframe = document.getElementById(frame_id);
                    if (!iframe) return;
                    if (iframe.tagName.toUpperCase() != 'IFRAME') {
                        iframe = iframe.getElementsByTagName('iframe')[0];
                        if (!iframe) return;
                    }
                }
                if (e.source === iframe.contentWindow) {
                    // Assume that the player is ready if we receive a
                    // message from the iframe
                    clearInterval(queue.poller);
                    queue.ready = true;
                    messageEvent(0, runOnceReady);
                    // .. and release the queue:
                    while (tmp = queue.shift()) {
                        callPlayer(frame_id, tmp[0], tmp[1]);
                    }
                }
            }, false);
        }
    } else if (iframe && iframe.contentWindow) {
        // When a function is supplied, just call it (like "onYouTubePlayerReady")
        if (func.call) return func();
        // Frame exists, send message
        iframe.contentWindow.postMessage(JSON.stringify({
            "event": "command",
            "func": func,
            "args": args || [],
            "id": frame_id
        }), "*");
    }
    /* IE8 does not support addEventListener... */
    function messageEvent(add, listener) {
        var w3 = add ? window.addEventListener : window.removeEventListener;
        w3 ?
            w3('message', listener, !1)
        :
            (add ? window.attachEvent : window.detachEvent)('onmessage', listener);
    }
}
    </script>

    <?php update_option('sln_calendar_page_show', 1)?>

<?php endif;?>

<?php if (current_user_can('export_reservations_csv_sln_calendar')): ?>
    <div class="row">
    <div class="col-xs-12 col-md-9">
        <form action="<?php echo admin_url('admin.php?page=' . SLN_Admin_Tools::PAGE) ?>" method="post">
        <h2><?php _e('Export reservations into a CSV file', 'salon-booking-system')?></h2>
        <div class="row">
            <?php
$f = $plugin->getSettings()->get('date_format');
$weekStart = $plugin->getSettings()->get('week_start');
$jsFormat = SLN_Enum_DateFormat::getJsFormat($f);
?>
            <div class="form-group col-xs-12 col-md-4 sln_datepicker sln-input--simple">
            <label for="<?php echo SLN_Form::makeID("export[from]") ?>"><?php _e('from', 'salon-booking-system')?></label>
            <input type="text" class="form-control sln-input" id="<?php echo SLN_Form::makeID("export[from]") ?>" name="export[from]"
                   required="required" data-format="<?php echo $jsFormat ?>" data-weekstart="<?php echo $weekStart ?>"
                   data-locale="<?php echo SLN_Plugin::getInstance()->getSettings()->getDateLocale() ?>"
           autocomplete="off"
            />
            </div>
            <div class="form-group col-xs-12 col-md-4 sln_datepicker sln-input--simple">
            <label for="<?php echo SLN_Form::makeID("export[to]") ?>"><?php _e('to', 'salon-booking-system')?></label>
            <input type="text" class="form-control sln-input" id="<?php echo SLN_Form::makeID("export[to]") ?>" name="export[to]"
                   required="required" data-format="<?php echo $jsFormat ?>" data-weekstart="<?php echo $weekStart ?>"
                   data-locale="<?php echo SLN_Plugin::getInstance()->getSettings()->getDateLocale() ?>"
           autocomplete="off"
            />
            </div>
            <div class="form-group col-xs-12">
            <button type="submit" id="action" name="sln-tools-export" value="export"
                class="sln-btn sln-btn--main sln-btn--big sln-btn--icon sln-icon--file">
                <?php _e('Export', 'salon-booking-system')?></button>
            </div>
        </div>
        </form>
    </div>
    <div class="col-xs-12 col-md-3 pull-right"></div>
    </div>
<?php endif;?>
<div class="row sln-calendar-sidebar">
<div class="col-xs-12 col-md-10">
    <h4><?php _e('Bookings status legend', 'salon-booking-system')?></h4>
<ul>
<li><span class="pull-left event event-warning"></span><?php echo SLN_Enum_BookingStatus::getLabel(SLN_Enum_BookingStatus::PENDING) ?></li>
<li><span class="pull-left event event-success"></span><?php echo SLN_Enum_BookingStatus::getLabel(SLN_Enum_BookingStatus::PAID) ?> <?php _e('or', 'salon-booking-system')?> <?php echo SLN_Enum_BookingStatus::getLabel(SLN_Enum_BookingStatus::CONFIRMED) ?></li>
<li><span class="pull-left event event-info"></span><?php echo SLN_Enum_BookingStatus::getLabel(SLN_Enum_BookingStatus::PAY_LATER) ?></li>
<li><span class="pull-left event event-danger"></span><?php echo SLN_Enum_BookingStatus::getLabel(SLN_Enum_BookingStatus::CANCELED) ?></li>
</ul>
<div class="clearfix"></div>
        </div>
    <div class="col-xs-12 col-md-2">
        <?php if ( ! in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ): ?>
        <div class="sln-help-button__block">
        <button class="sln-help-button sln-btn sln-btn--nobkg sln-btn--big sln-btn--icon sln-icon--helpchat sln-btn--icon--al visible-md-inline-block visible-lg-inline-block"><?php _e('Do you need help ?', 'salon-booking-system')?></button>
        <button class="sln-help-button sln-btn sln-btn--mainmedium sln-btn--small--round sln-btn--icon  sln-icon--helpchat sln-btn--icon--al hidden-md hidden-lg"><?php _e('Do you need help ?', 'salon-booking-system')?> </button>
    </div>
    <?php endif; ?>
    </div>
</div>
</div>
</div>
<?php if ( ! in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ): ?>
<script defer>
    window.Userback = window.Userback || {};
    Userback.access_token = '33731|64310|7TOMg95VWdhaFTyY2oCZrnrV3';
    (function(d) {
    var s = d.createElement('script');s.async = true;
    s.src = 'https://static.userback.io/widget/v1.js';
    (d.head || d.body).appendChild(s);
    })(document);
</script>
<?php endif; ?>