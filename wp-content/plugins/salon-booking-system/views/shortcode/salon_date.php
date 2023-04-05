<?php
/**
 * @var SLN_Plugin $plugin
 * @var string $formAction
 * @var string $submitName
 */
if ($plugin->getSettings()->isDisabled()) {
    $message = $plugin->getSettings()->getDisabledMessage();
    ?>
    <div class="sln-alert sln-alert--paddingleft sln-alert--problem">
        <?php echo empty($message) ? __('On-line booking is disabled', 'salon-booking-system') : $message ?>
    </div>
    <?php
} else {
    SLN_TimeFunc::startRealTimezone();
    $bb = $plugin->getBookingBuilder();
    $intervals = $plugin->getIntervals($bb->getDateTime());
    $date = $intervals->getSuggestedDate();
    $customerTimezone = $plugin->getSettings()->isDisplaySlotsCustomerTimezone() ? $bb->get('customer_timezone') : '';

    if ($plugin->getSettings()->isFormStepsAltOrder()) {
        $obj = new SLN_Action_Ajax_CheckDateAlt($plugin);
        $obj->setDate(SLN_Func::filter($date, 'date'))->setTime(SLN_Func::filter($date, 'time'));
        $intervalsArray = $obj->getIntervalsArray($customerTimezone);
        $date = new SLN_DateTime($intervalsArray['suggestedYear'].'-'.$intervalsArray['suggestedMonth'].'-'.$intervalsArray['suggestedDay'].' '.$intervalsArray['suggestedTime']);
        $dateTime = $customerTimezone ? (new SLN_DateTime($date, new DateTimeZone($customerTimezone)))->setTimezone(SLN_DateTime::getWpTimezone()) : $date;
        $errors = $obj->checkDateTimeServicesAndAttendants($bb->getAttendantsIds(), $dateTime);
    } else {
        $intervalsArray = $intervals->toArray($customerTimezone);
    }

    if (!$plugin->getSettings()->isFormStepsAltOrder() && !$intervalsArray['times']):
        $hb = $plugin->getAvailabilityHelper()->getHoursBeforeHelper()->getToDate();
        ?>
        <div class="sln-alert sln-alert--problem">
            <p><?php echo __('No more slots available until', 'salon-booking-system') ?> <?php echo $plugin->format(
                )->datetime($hb) ?></p>
        </div>
    <?php else: ?>
        <form method="post" action="<?php echo $formAction ?>" id="salon-step-date"
              data-intervals="<?php echo esc_attr(json_encode($intervalsArray)); ?>"
              <?php if(true == (bool)SLN_Plugin::getInstance()->getSettings()->get('debug') && current_user_can( 'administrator' ) ): ?>
                data-debug="<?php echo esc_attr( json_encode( SLN_Helper_Availability_AdminRuleLog::getInstance()->getDateLog() ) ); ?>"
              <?php endif ?>>
            <?php echo apply_filters('sln.booking.salon.date-step.add-params-html', '') ?>
            <?php
            $args = array(
                'label'        => __('When do you want to come?', 'salon-booking-system'),
                'tag'          => 'h2',
                'textClasses'  => 'salon-step-title',
                'inputClasses' => '',
                'tagClasses'   => 'salon-step-title',
            );
            echo $plugin->loadView('shortcode/_editable_snippet', $args);
            ?>
            <?php include '_salon_date_pickers.php' ?>
            <?php include '_errors.php'; ?>
	    <?php include '_additional_errors.php'; ?>
            <input type="hidden" name="sln[customer_timezone]" value="<?php echo $bb->get('customer_timezone') ?>">
            <?php if(true == (bool)SLN_Plugin::getInstance()->getSettings()->get('debug') && current_user_can( 'administrator' ) ): ?>
                <div id="sln-debug-div">
                    <div id="sln-debug-sticky-panel" style="width: 100%">
                        <div id="close-debug-table"><?php _e( 'Close', 'salon-booking-system') ?></div>
                        <input type="hidden" name="sln[debug]" value="1">
                        <div id="disable-debug-table"><?php _e( 'Disable', 'salone-booking-system' ) ?></div>
                        <nav class="sln-inpage_navbar_inner">
                            <ul id="sln-settings-links" class="nav nav-pills sln-inpage_navbar">
                                <li class="nav-item sln-inpage_navbaritem"><a href=<?php echo get_admin_url(). '/admin.php?page=salon-settings&tab=booking'; ?> class="nav-link nav-link1 sln-inpage_navbarlink" target="_blank"><?php _e( 'Booking rules', 'salon-booking-system' ) ?></a></li>
                                <li class="nav-item sln-inpage_navbaritem"><a href=<?php echo get_admin_url(). '/edit.php?post_type=sln_attendant' ?> class="nav-link nav-link1 sln-inpage_navbarlink" target="_blank"><?php _e( 'Assistants', 'salon-booking-system' ) ?></a></li>
                                <li class="nav-item sln-inpage_navbaritem"><a href=<?php echo get_admin_url(). '/edit.php?post_type=sln_service' ?> class="nav-link nav-link1 sln-inpage_navbarlink" target="_blank"><?php _e( 'Services', 'salon-booking-system') ?></a></li>
                            </ul>
                        </nav>
                        <div class="sln-debug-move"><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>
                    </div>
                    <div id="sln-debug-attendants" class="sln-row">
                        <?php foreach(SLN_Helper_Availability_AdminRuleLog::getInstance()->getAttendats() as $attendant_deb): ?>
                            <div class=sln-debug-time-slote><?php echo $attendant_deb->getName(); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div id="sln-debug-table">
                        <?php foreach( SLN_Helper_Availability_AdminRuleLog::getInstance()->getLog() as $time => $rules ): ?>
                            <div class="sln-debug-time-slote">
                                <div class="sln-debug-popup">
                                    <?php $failedRule = '';
                                        foreach( $rules as $ruleName => $ruleValue ){
                                        echo '<p class="'. ( (!$ruleValue) ? 'sln-debug--failed"':'"' ).'>'. $ruleName. '</p>';
                                        if( !(bool)$ruleValue && empty( $failedRule ) ){
                                            $failedRule = $ruleName;
                                        }
                                    } ?>
                                </div>
                                <div class="sln-debug-time <?php echo ( !empty($failedRule) ) ? 'sln-debug--failed"' : '"' ; ?>">
                                    <?php echo "<p title=\"$failedRule\">". $time. '</p>'; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="sln-debug-notifications"></div>
                    <?php SLN_Helper_Availability_AdminRuleLog::getInstance()->clear(); ?>
                </div>
            <?php endif ?>
        </form>
    <?php endif ?>
    <?php
}
