<div class="row sln-summary">
    <div class="col-xs-12">
        <div class="row sln-summary-row">
            <div class="col-xs-12 sln-data-desc">
                <?php
                $args = array(
                    'label'        => __('Date and time booked', 'salon-booking-system'),
                    'tag'          => 'span',
                    'textClasses'  => 'text-min label',
                    'inputClasses' => 'input-min',
                    'tagClasses'   => 'label',
                );
                echo $plugin->loadView('shortcode/_editable_snippet', $args);
                ?>
            </div>
            <div class="col-xs-12 sln-data-val">
                <?php echo $plugin->format()->date($datetime); ?> / <?php echo $plugin->format()->time($datetime) ?>
            </div>
            <div class="col-xs-12"><hr></div>
        </div>
        <?php if($attendants = $bb->getAttendants(true)) :  ?>
            <div class="row sln-summary-row">
                <div class="col-xs-12 sln-data-desc">
                    <?php
                    $args = array(
                        'label'        => __('Assistants', 'salon-booking-system'),
                        'tag'          => 'span',
                        'textClasses'  => 'text-min label',
                        'inputClasses' => 'input-min',
                        'tagClasses'   => 'label',
                    );
                    echo $plugin->loadView('shortcode/_editable_snippet', $args);
                    ?>
                </div>
                <div class="col-xs-12 sln-data-val">
                    <?php
                        $names = array();
                        foreach($attendants as $att) {
                            if(!is_array($att)){
                                $names[] = $att->getName();
                            }else{
                                $names = array_merge($names, SLN_Wrapper_Attendant::getArrayAttendantsValue('getName', $att));
                            }
                        }
                        echo implode(', ', $names);
                    ?>
                    </div>
                <div class="col-xs-12"><hr></div>
            </div>
        <?php // IF ASSISTANT // END
        endif ?>
        <div class="row sln-summary-row">
            <div class="col-xs-12 sln-data-desc">
                <?php
                $args = array(
                    'label'        => __('Services booked', 'salon-booking-system'),
                    'tag'          => 'span',
                    'textClasses'  => 'text-min label',
                    'inputClasses' => 'input-min',
                    'tagClasses'   => 'label',
                );
                echo $plugin->loadView('shortcode/_editable_snippet', $args);
                ?>
            </div>
            <div class="col-xs-12 sln-data-val">
                <ul class="sln-list--dashed">
                    <?php foreach ($bb->getServices() as $service): ?>
                        <li> <span class="service-label"><?php echo $service->getName(); ?></span>
                            <?php if($showPrices){?>
                                <?php $attendantID = isset($bb->getAttendantsIds()[$service->getId()]) ? $bb->getAttendantsIds()[$service->getId()] : null; ?>
                                <?php $servicePrice = $service->getVariablePriceEnabled() && $service->getVariablePrice($attendantID) !== '' ? $service->getVariablePrice($attendantID) : $service->getPrice() ?>
                                <small> (<?php echo $plugin->format()->moneyFormatted($servicePrice) ?>)</small>
                            <?php } ?>
                            <?php if ($bb->getCountService($service->getId()) && $bb->getCountService($service->getId()) > 1): ?>
                               <span class="sln-summary-variable-duration">
                                    <span class="sln-summary-variable-duration--divider">x</span>
                                    <?php echo $bb->getCountService($service->getId()) ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
            <div class="col-xs-12"><hr></div>
        </div>
        <?php do_action('sln.template.summary.before_total_amount', $bb, $size); ?>
	<?php if ($isTipRequestEnabled): ?>
	    <?php include '_salon_summary_show_tips.php'; ?>
	<?php endif; ?>
    <?php if($settings->get('enable_booking_tax_calculation')){
        include '_salon_summary_show_tax.php';
    } ?>
    </div>
    <div class="col-xs-12 sln-total">
        <?php if($showPrices){?>
            <div class="row">
            <h3 class="col-xs-6 sln-total-label"><?php _e('Total amount', 'salon-booking-system') ?></h3>
            <h3 class="col-xs-6 sln-total-price"><?php echo $plugin->format()->moneyFormatted(
                    $plugin->getBookingBuilder()->getTotal()
                ) ?> </h3></div>
        <?php }; ?>
    </div>
    <?php do_action('sln.template.summary.after_total_amount', $bb, $size); ?>
    <?php if ($isTipRequestEnabled): ?>
	<?php include '_salon_summary_add_tips.php'; ?>
    <?php endif; ?>
    <div class="col-xs-12 sln-input sln-input--simple sln-summary__message">
        <?php
        $label = __('Leave a message.', 'salon-booking-system');
        $args = array(
            'label'        => __('Leave a message.', 'salon-booking-system'),
            'tag'          => 'label',
            'textClasses'  => '',
            'inputClasses' => '',
            'tagClasses'   => '',
        );
        echo $plugin->loadView('shortcode/_editable_snippet', $args);
        ?>
        <?php SLN_Form::fieldTextarea(
            'sln[note]',
            $bb->get('note'),
            array('attrs' => array('placeholder' => __('Leave a message', 'salon-booking-system')))
        ); ?>
    </div>
    <?php do_action('sln.template.summary.before_terms', $bb, $size); ?>
    <div class="col-xs-12  sln-summary__terms">
        <p><strong><?php _e('Terms & Conditions','salon-booking-system')?></strong><br><?php echo $plugin->getSettings()->get('gen_timetable')
            /*_e(
                'In case of delay of arrival. we will wait a maximum of 10 minutes from booking time. Then we will release your reservation',
                'salon-booking-system'
            )*/ ?></p>
    </div>
    <?php do_action('sln.template.summary.after_terms', $bb, $size); ?>
</div>
<div class="row">
    <div class="col-xs-12 sln-input sln-input--action">
        <label for="login_name">&nbsp;</label>
        <?php $nextLabel = __('Next step', 'salon-booking-system');
        include "_form_actions.php" ?>
    </div>
</div>
