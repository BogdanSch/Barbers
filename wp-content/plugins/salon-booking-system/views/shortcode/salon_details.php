<?php
/**
 * @var SLN_Plugin                $plugin
 * @var string                    $formAction
 * @var string                    $submitName
 * @var SLN_Shortcode_Salon_Step $step
 */
$bb = $plugin->getBookingBuilder();
$style = $step->getShortcode()->getStyleShortcode();
$size = SLN_Enum_ShortcodeStyle::getSize($style);
global $current_user;
wp_get_current_user();

$current     = $step->getShortcode()->getCurrentStep();
$ajaxEnabled = $plugin->getSettings()->isAjaxEnabled();

$bookingDetailsPageUrl = add_query_arg(array('sln_step_page' => 'details', 'submit_details' => 'next'), get_permalink($plugin->getSettings()->getPayPageId()));

$fbLoginEnabled = $plugin->getSettings()->get('enabled_fb_login');

ob_start();
?>
<label for="login_name"><?php _e('E-mail', 'salon-booking-system') ?></label>
<input name="login_name" type="text" class="sln-input sln-input--text"/>
<span class="help-block"><a href="<?php echo wp_lostpassword_url() ?>" class="tec-link"><?php _e('Forgot password?', 'salon-booking-system') ?></a></span>
<?php
$fieldEmail = ob_get_clean();

ob_start();
?>
<label for="login_password"><?php _e('Password', 'salon-booking-system') ?></label>
<input name="login_password" type="password" class="sln-input sln-input--text"/>
<?php
$fieldPassword = ob_get_clean();

?>
<?php if (!is_user_logged_in()): ?>
    <?php
    if (!$plugin->getSettings()->get('enabled_force_guest_checkout')): ?>
    <form method="post" action="<?php echo $formAction ?>" role="form" enctype="multipart/form-data" id="salon-step-details">
        <?php echo apply_filters('sln.booking.salon.details-step.add-params-html', '') ?>
        <h2 class="salon-step-title"><?php _e('Returning customer?', 'salon-booking-system') ?> <?php _e('Please, log-in.', 'salon-booking-system') ?> </h2>
    <?php
    if ($size == '900') { ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-4 sln-input sln-input--simple"><?php echo $fieldEmail?></div>
            <div class="col-xs-12 col-sm-6 col-md-4 sln-input sln-input--simple"><?php echo $fieldPassword?></div>
            <div class="col-xs-12 col-sm-6 col-md-4 pull-right sln-input sln-input--simple">
                <label for="login_name">&nbsp;</label>
                <div class="sln-btn sln-btn--emphasis sln-btn--medium sln-btn--fullwidth">
                    <button type="submit"
                        <?php if ($ajaxEnabled): ?>
                            data-salon-data="<?php echo "sln_step_page={$current}&{$submitName}=next" ?>" data-salon-toggle="next"
                        <?php endif ?>
                            name="<?php echo $submitName ?>" value="next">
                        <?php echo __('Login', 'salon-booking-system') ?> <i class="glyphicon glyphicon-user"></i>
                    </button>
                </div>
		<?php if ($fbLoginEnabled): ?>
		    <a href="<?php echo add_query_arg(array('referrer' => urlencode($bookingDetailsPageUrl)), SLN_Helper_FacebookLogin::getRedirectUri()) ?>" class="sln-btn sln-btn--fullwidth sln-btn--nobkg sln-btn--medium sln-btn--fb"><svg class="sln-fblogin--icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0v24h24v-24h-24zm16 7h-1.923c-.616 0-1.077.252-1.077.889v1.111h3l-.239 3h-2.761v8h-3v-8h-2v-3h2v-1.923c0-2.022 1.064-3.077 3.461-3.077h2.539v3z"/></svg><?php _e('log-in with Facebook', 'salon-booking-system'); ?></a>
		<?php endif ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12"><?php include '_errors.php'; ?></div>
        </div>
    <?php
    // IF SIZE 900 // END
    } else if ($size == '600') { ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6 sln-input sln-input--simple"><?php echo $fieldEmail?></div>
            <div class="col-xs-12 col-sm-6 sln-input sln-input--simple"><?php echo $fieldPassword?></div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-6"></div>
            <div class="col-xs-12 col-sm-6 sln-input sln-input--simple">
                <div class="sln-btn sln-btn--emphasis sln-btn--medium sln-btn--fullwidth">
                <button type="submit"
                    <?php if ($ajaxEnabled): ?>
                        data-salon-data="<?php echo "sln_step_page={$current}&{$submitName}=next" ?>" data-salon-toggle="next"
                    <?php endif ?>
                        name="<?php echo $submitName ?>" value="next">
                    <?php echo __('Login','salon-booking-system')?> <i class="glyphicon glyphicon-user"></i>
                </button>
                </div>
		<?php if ($fbLoginEnabled): ?>
		    <a href="<?php echo add_query_arg(array('referrer' => urlencode($bookingDetailsPageUrl)), SLN_Helper_FacebookLogin::getRedirectUri()) ?>" class="sln-btn sln-btn--fullwidth sln-btn--nobkg sln-btn--medium sln-btn--fb"><svg class="sln-fblogin--icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0v24h24v-24h-24zm16 7h-1.923c-.616 0-1.077.252-1.077.889v1.111h3l-.239 3h-2.761v8h-3v-8h-2v-3h2v-1.923c0-2.022 1.064-3.077 3.461-3.077h2.539v3z"/></svg><?php _e('log-in with Facebook', 'salon-booking-system'); ?></a>
		<?php endif ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12"><?php include '_errors.php'; ?></div>
        </div>
    <?php
    // IF SIZE 600 // END
    } else if ($size == '400') { ?>
        <div class="row">
            <div class="col-xs-12 sln-input sln-input--simple"><?php echo $fieldEmail?></div>
            <div class="col-xs-12 sln-input sln-input--simple"><?php echo $fieldPassword?></div>
            <div class="col-xs-12 sln-input sln-input--simple">
                <label for="login_name">&nbsp;</label>
                <div class="sln-btn sln-btn--emphasis sln-btn--medium sln-btn--fullwidth">
                <button type="submit"
                    <?php if ($ajaxEnabled): ?>
                        data-salon-data="<?php echo "sln_step_page={$current}&{$submitName}=next" ?>" data-salon-toggle="next"
                    <?php endif ?>
                        name="<?php echo $submitName ?>" value="next">
                    <?php echo __('Login','salon-booking-system')?> <i class="glyphicon glyphicon-user"></i>
                </button>
                </div>
		<?php if ($fbLoginEnabled): ?>
		    <a href="<?php echo add_query_arg(array('referrer' => urlencode($bookingDetailsPageUrl)), SLN_Helper_FacebookLogin::getRedirectUri()) ?>" class="sln-btn sln-btn--fullwidth sln-btn--nobkg sln-btn--medium sln-btn--fb"><svg class="sln-fblogin--icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0v24h24v-24h-24zm16 7h-1.923c-.616 0-1.077.252-1.077.889v1.111h3l-.239 3h-2.761v8h-3v-8h-2v-3h2v-1.923c0-2.022 1.064-3.077 3.461-3.077h2.539v3z"/></svg><?php _e('log-in with Facebook', 'salon-booking-system'); ?></a>
		<?php endif ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12"><?php include '_errors.php'; ?></div>
        </div>
    <?php
    // IF SIZE 400 // END
    } else  { ?>

    <?php
    // ELSE // END
    }  ?>
    </form>
<?php endif; ?>
    <form method="post" action="<?php echo $formAction ?>" role="form" enctype="multipart/form-data" id="salon-step-details-new">
        <?php echo apply_filters('sln.booking.salon.details-step.add-params-html', '') ?>
        <div class="row">
            <?php if($plugin->getSettings()->get('enabled_force_guest_checkout')): ?>
                <h2 class="salon-step-title"><?php _e('Please fill out the form to checkout', 'salon-booking-system') ?></h2>
                <?php SLN_Form::fieldCheckbox(
                    'sln[no_user_account]',
                    $bb->get('no_user_account'),
                    array(
                        'type' => 'hidden',
                        'attrs' => array(
                            'checked' => 'checked',
                            'style' => 'display:none'
                        )
                    )
                ) ?>
            <?php elseif($plugin->getSettings()->get('enabled_guest_checkout')): ?>
                <div class="col-xs-2 col-sm-1 sln-checkbox">
                    <div class="sln-checkbox">
                        <?php SLN_Form::fieldCheckbox(
                            'sln[no_user_account]',
                            $bb->get('no_user_account'),
                            array()
                        ) ?>
                        <label for="<?php echo SLN_Form::makeID('sln[no_user_account]') ?>"></label>
                    </div>
                </div>
                <div class="col-xs-12 col-md-11">
                    <label for="<?php echo SLN_Form::makeID('sln[no_user_account]') ?>"><h2 class="salon-step-title"><?php _e('checkout as a guest', 'salon-booking-system') ?>, <?php _e('no account will be created', 'salon-booking-system') ?></h2></label>
                </div>
            <?php else: ?>
            <div class="col-xs-12">
                    <h2 class="salon-step-title"><?php _e('Checkout as a guest', 'salon-booking-system') ?>, <?php _e('An account will be automatically created', 'salon-booking-system') ?></h2>
                </div>
            <?php endif; ?>

        </div>
    <?php
        $fields = $plugin->getSettings()->get('enabled_force_guest_checkout') ?  SLN_Enum_CheckoutFields::forGuestCheckout() : SLN_Enum_CheckoutFields::forDetailsStep()->appendPassword();
        foreach($fields as $field) { //remove excessive quotes escaping
            if(!$field->isDefault()) {
                $field->offsetSet('label', stripcslashes($field->offsetGet('label')));
                $field->offsetSet('default_value', stripcslashes($field->offsetGet('default_value')));
            }
        }
    if ($size == '900') { ?>
    <div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="row">
            <?php
                        foreach ($fields as $key => $field):  ?>
                            <?php
                            $value = !$bb->get($key) && null !== $field['default_value'] ? $field['default_value'] : $bb->get($key) ;
                            $type = $field['type'];
                            $width = $field['width'];
            ?>
		                    <?php if($key === 'password') do_action('sln.template.details.before_password', $bb, $size); ?>
                            <?php if($key === 'password') echo '</div><div class="row">'; // close previous row & open next ?>
                            <div class="col-xs-12 col-sm-<?php echo $key == 'address' ? 12 : $width ?> <?php echo 'field-'.$key ?> <?php if($type !== 'checkbox'){ echo 'sln-input sln-input--simple'; } ?> <?php echo $type ? 'sln-'.$type  : '' ?> <?php echo $field->isCustomer() && $field->isAdditional() ? 'sln-customer-fields' : '' ?>">
                                <?php if ($type === 'html'): ?>
                                    <?php echo $field['default_value'] ?>
                                <?php else: ?>
                                    <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                                    <?php if(($key == 'phone') && ($prefix = $bb->get('sms_prefix') ? $bb->get('sms_prefix') : $plugin->getSettings()->get('sms_prefix'))): ?>
                        <div class="input-group sln-input-group">
                            <span class="input-group-addon sln-input--addon hide"><?php echo $prefix?></span>
                            <?php SLN_Form::fieldText('sln[sms_prefix]', $prefix, array('type' => 'hidden')); ?>
                            <?php endif ?>
                        <?php
                                        if(strpos($key, 'password') === 0){
                                            SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => true, 'type' => 'password'));
                                        } else if(strpos($key, 'email') === 0){
                                           SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden(), 'type' => 'email'));
                           } else {
                            if($type){
                                $additional_opts = array(
                                                'sln[' . $key . ']', $value,
                                                array('required' => $field->isRequiredNotHidden())
                                );
                                $method_name = 'field'.ucfirst($type);
                                if($type === 'checkbox') {
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                    $method_name = $method_name .'Button';
                                }

                                if($type === 'select') {
				    $additional_opts[2]['attrs'] = array(
					'data-placeholder' => __('Select an option', 'salon-booking-system'),
				    );
				    $additional_opts[2]['empty_value'] = true;
                                                $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);

                                }
call_user_func_array(array('SLN_Form',$method_name), $additional_opts );
}else{
                                            SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden()));
                                }
                            }
                        ?>
                                    <?php if(($key == 'phone') && !empty($prefix)):?>
                        </div>
                        <?php endif ?>
                    <?php endif ?>
                </div>

            <?php endforeach ?>
	    <?php do_action('sln.template.details.after_form', $bb, $size); ?>
        </div>
    </div>
    <div class="col-xs-12 col-md-4 sln-input sln-input--action  sln-box--main sln-box--formactions">
        <label for="login_name">&nbsp;</label>
        <?php include "_form_actions.php" ?>
    </div>
    </div>
    <div class="row">
        <div class="col-xs-12"><?php include '_errors.php'; ?></div>
    </div>
    <?php
    // IF SIZE 900 // END
    } else if ($size == '600') { ?>
    <div class="row">
                    <?php
                    foreach ($fields as $key => $field):   ?>
                        <?php
                        $value = !$bb->get($key) && null !== $field['default_value'] ? $field['default_value'] : $bb->get($key) ;
                        $type = $field['type'];
                        $width = $field['width'];
                 ?>
                        <?php if($key === 'password') echo '</div><div class="row">'; // close previous row & open next ?>
		                <?php if($key === 'password') do_action('sln.template.details.before_password', $bb, $size); ?>
                        <div class="col-xs-12 col-sm-6 col-md-<?php echo $key == 'address' ? 12 :  $width ?> <?php echo 'field-'.$key ?> <?php if($type !== 'checkbox'){ echo 'sln-input sln-input--simple'; } ?> <?php echo $type ? 'sln-'.$type  : '' ?>  <?php echo  $field->isCustomer() && $field->isAdditional() ? 'sln-customer-fields' : '' ?>">
                            <?php if ($type === 'html'): ?>
                                <?php echo $field['default_value'] ?>
                            <?php else: ?>
                                <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                                <?php if(($key == 'phone') && ($prefix = $bb->get('sms_prefix') ? $bb->get('sms_prefix') : $plugin->getSettings()->get('sms_prefix'))): ?>
                        <div class="input-group sln-input-group">
                            <span class="input-group-addon sln-input--addon hide"><?php echo $prefix?></span>
                            <?php SLN_Form::fieldText('sln[sms_prefix]', $prefix, array('type' => 'hidden')); ?>
                            <?php endif ?>

                        <?php
                                    if(strpos($key, 'password') === 0){
                                        SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => true, 'type' => 'password'));
                                    } else if(strpos($key, 'email') === 0){
                                       SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden(), 'type' => 'email'));
                           } else {
                            if($type){
                                $additional_opts = array(
                                            'sln[' . $key . ']', $value,
                                            array('required' => $field->isRequiredNotHidden())
                                );
                                $method_name = 'field'.ucfirst($type);
                                if($type === 'checkbox') {
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                    $method_name = $method_name .'Button';
                                }

                                if($type === 'select') {
				    $additional_opts[2]['attrs'] = array(
					'data-placeholder' => __('Select an option', 'salon-booking-system'),
				    );
				    $additional_opts[2]['empty_value'] = true;
                                            $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);

                                }
call_user_func_array(array('SLN_Form',$method_name), $additional_opts );
}else{
                                        SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden()));
                                }
                            }
                        ?>
                                <?php if(($key == 'phone') && !empty($prefix)):?>
                        </div>
                        <?php endif ?>
                    <?php endif ?>
                </div>

            <?php endforeach ?>
	    <?php do_action('sln.template.details.after_form', $bb, $size); ?>
    </div>


    <div class="row sln-box--main sln-box--formactions">
           <div class="col-xs-12">
           <?php include "_form_actions.php" ?></div>
        </div>
    <div class="row">
        <div class="col-xs-12"><?php include '_errors.php'; ?></div>
    </div>
    <?php
    // IF SIZE 600 // END
    } else if ($size == '400') { ?>
    <div class="row">
                    <?php
                    foreach ($fields as $key => $field):
                        $value = !$bb->get($key) && null !== $field['default_value'] ? $field['default_value'] : $bb->get($key) ; ?>
                        <?php
                        $type = $field['type']; ?>
                        <?php if($key === 'password') echo '</div><div class="row">'; // close previous row & open next ?>
		                <?php if($key === 'password') do_action('sln.template.details.before_password', $bb, $size); ?>
                        <div class="col-xs-12 <?php echo 'field-'.$key ?> <?php if($type !== 'checkbox'){ echo 'sln-input sln-input--simple'; } ?> <?php echo $type ? 'sln-'.$type  : '' ?><?php echo $field->isCustomer() && $field->isAdditional() ? 'sln-customer-fields' : '' ?>">
                            <?php if ($type === 'html'): ?>
                                <?php echo $field['default_value'] ?>
                            <?php else: ?>
                                <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                                <?php if(($key == 'phone') && ($prefix = $bb->get('sms_prefix') ? $bb->get('sms_prefix') : $plugin->getSettings()->get('sms_prefix'))): ?>
                        <div class="input-group sln-input-group">
                            <span class="input-group-addon sln-input--addon hide"><?php echo $prefix?></span>
                            <?php SLN_Form::fieldText('sln[sms_prefix]', $prefix, array('type' => 'hidden')); ?>
                            <?php endif ?>
                        <?php
                                    if(strpos($key, 'password') === 0){
                                        SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => true, 'type' => 'password'));
                                    } else if(strpos($key, 'email') === 0){
                                       SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden(), 'type' => 'email'));
                           } else {
                            if($type){
                                $additional_opts = array(
                                            'sln[' . $key . ']', $value,
                                            array('required' => $field->isRequiredNotHidden())
                                );
                                $method_name = 'field'.ucfirst($type);
                                if($type === 'checkbox') {
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                    $method_name = $method_name .'Button';
                                }

                                if($type === 'select') {
				    $additional_opts[2]['attrs'] = array(
					'data-placeholder' => __('Select an option', 'salon-booking-system'),
				    );
				    $additional_opts[2]['empty_value'] = true;
                                            $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);

                                }
call_user_func_array(array('SLN_Form',$method_name), $additional_opts );
}else{
                                        SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden()));
                                }
                            }
                        ?>
                                <?php if(($key == 'phone') && !empty($prefix)):?>
                        </div>
                        <?php endif ?>
                    <?php endif ?>
                </div>

            <?php endforeach ?>
	    <?php do_action('sln.template.details.after_form', $bb, $size); ?>
            <div class="col-xs-12  sln-box--formactions"><label for="login_name">&nbsp;</label><?php include "_form_actions.php" ?></div>
    </div>
    <div class="row">
        <div class="col-xs-12"><?php include '_errors.php'; ?></div>
    </div>
    <?php
    // IF SIZE 400 // END
    } else  { ?>

    <?php
    // ELSE // END
    }  ?>
    </form>
<?php else: ?>

    <form method="post" action="<?php echo $formAction ?>" role="form" enctype="multipart/form-data">
        <?php echo apply_filters('sln.booking.salon.details-step.add-params-html', '') ?>
        <?php
        $args = array(
            'label'        => __('Checkout', 'salon-booking-system'),
            'tag'          => 'h2',
            'textClasses'  => 'salon-step-title',
            'inputClasses' => '',
            'tagClasses'   => 'salon-step-title',
        );
        echo $plugin->loadView('shortcode/_editable_snippet', $args);
        $fields = SLN_Enum_CheckoutFields::forDetailsStep()->filter('booking_hidden',false);
        foreach($fields as $field) { //remove excessive quotes escaping
            if(!$field->isDefault()) {
                $field->offsetSet('default_value', stripcslashes($field->offsetGet('default_value')));
            }
        }
        ?>
    <?php
    if ($size == '900') { ?>
    <div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="row">
                        <?php
                        foreach ( $fields as $key => $field):
                            $value = !$bb->get($key) && null !== $field['default_value'] ? $field['default_value'] : $bb->get($key) ;?>
                            <?php
                            $type = $field['type'];
                            $width = $field['width'];
                ?>
                            <div class="col-xs-12 col-sm-6 col-md-<?php echo $key == 'address' ? 12 : $width ?> <?php echo 'field-'.$key ?> <?php if($type !== 'checkbox'){ echo 'sln-input sln-input--simple'; } ?> <?php echo $type ? 'sln-'.$type  : '' ?>">
                                <?php if ($type === 'html'): ?>
                                    <?php echo $field['default_value'] ?>
                                <?php else: ?>
                                    <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                                    <?php if(($key == 'phone') && ($prefix = $bb->get('sms_prefix') ? $bb->get('sms_prefix') : $plugin->getSettings()->get('sms_prefix'))): ?>
                        <div class="input-group sln-input-group">
                            <span class="input-group-addon sln-input--addon hide"><?php echo $prefix?></span>
                            <?php SLN_Form::fieldText('sln[sms_prefix]', $prefix, array('type' => 'hidden')); ?>
                        <?php endif ?>
                        <?php
                                       if(strpos($key, 'email') === 0){
                                           SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden(), 'type' => 'email'));
                           } else{
                            if($type){
                                $additional_opts = array(
                                                'sln[' . $key . ']', $value,
                                                array('required' => $field->isRequiredNotHidden())
                                );
                                $method_name = 'field'.ucfirst($type);
                                if($type === 'checkbox') {
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                    $method_name = $method_name .'Button';
                                }

                                if($type === 'select') {
				    $additional_opts[2]['attrs'] = array(
					'data-placeholder' => __('Select an option', 'salon-booking-system'),
				    );
				    $additional_opts[2]['empty_value'] = true;
                                                $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);

                                }
call_user_func_array(array('SLN_Form',$method_name), $additional_opts );
}else{
                                           SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden()));
                            }
                           }
                        ?>
                                    <?php if(($key == 'phone') && !empty($prefix)):?>
                                </div>
                            <?php endif ?>
                    <?php endif ?>
                </div>

            <?php endforeach ?>
	    <?php do_action('sln.template.details.after_form', $bb, $size); ?>
        </div>
    </div>
    <div class="col-xs-12 col-md-4 sln-input sln-input--action sln-box--formactions">
        <label for="login_name">&nbsp;</label>
        <?php include "_form_actions.php" ?>
    </div>
    </div>
    <div class="row">
        <div class="col-xs-12"><?php include '_errors.php'; ?></div>
    </div>
    <?php
    // IF SIZE 900 // END
    } else if ($size == '600') { ?>
    <div class="row">
                <?php foreach ($fields as $key => $field):
                    $value = !$bb->get($key) && null !== $field['default_value'] ? $field['default_value'] : $bb->get($key) ;
                    $type = $field['type'];
                    $width = $field['width'];
                ?>
                    <div class="col-xs-12 col-sm-6 col-md-<?php echo $key == 'address' ? 12 : $width ?> <?php echo 'field-'.$key ?> <?php if($type !== 'checkbox'){ echo 'sln-input sln-input--simple'; } ?> <?php echo $type ? 'sln-'.$type  : '' ?>">
                        <?php if ($type === 'html'): ?>
                            <?php echo $field['default_value'] ?>
                        <?php else: ?>
                            <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                            <?php if(($key == 'phone') && ($prefix = $bb->get('sms_prefix') ? $bb->get('sms_prefix') : $plugin->getSettings()->get('sms_prefix'))): ?>
                        <div class="input-group sln-input-group">
                            <span class="input-group-addon sln-input--addon hide"><?php echo $prefix?></span>
                            <?php SLN_Form::fieldText('sln[sms_prefix]', $prefix, array('type' => 'hidden')); ?>
                        <?php endif ?>
                        <?php
                               if(strpos($key, 'email') === 0){
                                   SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden(), 'type' => 'email'));
                           } else{
                            if($type){
                                $additional_opts = array(
                                        'sln[' . $key . ']', $value,
                                        array('required' => $field->isRequiredNotHidden())
                                );
                                $method_name = 'field'.ucfirst($type);
                                if($type === 'checkbox') {
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                    $method_name = $method_name .'Button';
                                }

                                if($type === 'select') {
                                    $additional_opts[2]['attrs'] = array(
                                        'data-placeholder' => __('Select an option', 'salon-booking-system'),
                                        );
                                    $additional_opts[2]['empty_value'] = true;
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);
                                }
                                if($type == 'file'){
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getFiles($current_user), $field->getFileType()], array_slice($additional_opts, 1), [true]);
                                }
                                call_user_func_array(array('SLN_Form',$method_name), $additional_opts );
                            }else{
                                   SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden()));
                            }
                           }
                        ?>
                            <?php if(($key == 'phone') && !empty($prefix)):?>
                                </div>
                            <?php endif ?>
                    <?php endif ?>
                </div>

            <?php endforeach ?>
	    <?php do_action('sln.template.details.after_form', $bb, $size); ?>
        </div>
    <div class="row sln-box--formactions">
                <div class="col-xs-12"><?php include "_form_actions.php" ?></div>
        </div>
    <div class="row">
        <div class="col-xs-12"><?php include '_errors.php'; ?></div>
    </div>
    <?php
    // IF SIZE 600 // END
    } else if ($size == '400') { ?>
    <div class="row">
                <?php foreach ($fields as $key => $field):
                    $value = !$bb->get($key) && null !== $field['default_value'] ? $field['default_value'] : $bb->get($key) ;?>
                    <?php
                    $type = $field['type']; ?>
                    <div class="col-xs-12 <?php echo 'field-'.$key ?> <?php if($type !== 'checkbox'){ echo 'sln-input sln-input--simple'; } ?> <?php echo $type ? 'sln-'.$type  : '' ?>">
                        <?php if ($type === 'html'): ?>
                            <?php echo $field['default_value'] ?>
                        <?php else: ?>
                            <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                            <?php if(($key == 'phone') && ($prefix = $bb->get('sms_prefix') ? $bb->get('sms_prefix') : $plugin->getSettings()->get('sms_prefix'))): ?>
                        <div class="input-group sln-input-group">
                            <span class="input-group-addon sln-input--addon hide"><?php echo $prefix?></span>
                            <?php SLN_Form::fieldText('sln[sms_prefix]', $prefix, array('type' => 'hidden')); ?>
                        <?php endif ?>
                        <?php
                               if(strpos($key, 'email') === 0){
                                   SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden(), 'type' => 'email'));
                           } else{
                            if($type){
                                $additional_opts = array(
                                        'sln[' . $key . ']', $value,
                                        array('required' => $field->isRequiredNotHidden())
                                );
                                $method_name = 'field'.ucfirst($type);
                                if($type === 'checkbox') {
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                    $method_name = $method_name .'Button';
                                }

                                if($type === 'select') {
				    $additional_opts[2]['attrs'] = array(
					'data-placeholder' => __('Select an option', 'salon-booking-system'),
				    );
				    $additional_opts[2]['empty_value'] = true;
                                        $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);

                                }
call_user_func_array(array('SLN_Form',$method_name), $additional_opts );
}else{
                                   SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden()));
                            }
                           }
                        ?>
                            <?php if(($key == 'phone') && !empty($prefix)):?>
                                </div>
                            <?php endif ?>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
	    <?php do_action('sln.template.details.after_form', $bb, $size); ?>
            <div class="col-xs-12 sln-box--formactions"><label for="login_name">&nbsp;</label><?php include "_form_actions.php" ?></div>
    </div>
    <div class="row">
        <div class="col-xs-12"><?php include '_errors.php'; ?></div>
    </div>
    <?php
    // IF SIZE 400 // END
    } else  { ?>
<div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="row">
                        <?php foreach ($fields as $key => $field):
                            $value = !$bb->get($key) && null !== $field['default_value'] ? $field['default_value'] : $bb->get($key) ; ?>
                            <?php
                            $type = $field['type'];
                             $width = $field['width']; ?>
                            <div class="col-xs-12 col-md-<?php echo $key == 'address' ? 12 : $width ?> <?php echo 'field-'.$key ?> <?php if($type !== 'checkbox'){ echo 'sln-input sln-input--simple'; } ?> <?php echo $type ? 'sln-'.$type  : '' ?>">
                                    <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                                    <?php if(($key == 'phone') && ($prefix = $plugin->getSettings()->get('sms_prefix'))): ?>
                        <div class="input-group sln-input-group">
                            <span class="input-group-addon sln-input--addon"><?php echo $prefix?></span>
                        <?php endif ?>
                        <?php
                                       if(strpos($key, 'email') === 0){
                                           SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden(), 'type' => 'email'));
                           } else{
                            if($type){
                                $additional_opts = array(
                                                'sln[' . $key . ']', $value,
                                                array('required' => $field->isRequiredNotHidden())
                                );
                                $method_name = 'field'.ucfirst($type);
                                if($type === 'checkbox') {
                                    $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                    $method_name = $method_name .'Button';
                                }

                                if($type === 'select') {
				    $additional_opts[2]['attrs'] = array(
					'data-placeholder' => __('Select an option', 'salon-booking-system'),
				    );
				    $additional_opts[2]['empty_value'] = true;
                                                $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);

                                }
call_user_func_array(array('SLN_Form',$method_name), $additional_opts );
}else{
                                           SLN_Form::fieldText('sln[' . $key . ']', $value, array('required' => $field->isRequiredNotHidden()));
                            }
                           }
                        ?>
                                    <?php if(($key == 'phone') && !empty($prefix)):?>
                                </div>
                            <?php endif ?>
                </div>

            <?php endforeach ?>
	    <?php do_action('sln.template.details.after_form', $bb, $size); ?>
        </div>
    </div>
    <div class="col-xs-12 col-md-4 sln-input sln-input--action sln-box--formactions">
        <label for="login_name">&nbsp;</label>
        <?php include "_form_actions.php" ?>
    </div>
    </div>
    <div class="row">
        <div class="col-xs-12"><?php include '_errors.php'; ?></div>
    </div>
    <?php
    // ELSE // END
    }  ?>
    </form>
<?php endif ?>

