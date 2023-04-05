<?php
$current_user = wp_get_current_user();
$plugin       = SLN_Plugin::getInstance();
$values       = array();
foreach (SLN_Enum_CheckoutFields::forCustomer()->appendSmsPrefix() as $key => $field) {
    $values[$key] = $field->getValue(get_current_user_id());
}
$errors = isset($sln_update_profile) && is_array($sln_update_profile) && isset($sln_update_profile['errors']) ? $sln_update_profile['errors'] : array();

$last_update = get_user_meta(get_current_user_id(), '_sln_last_update', true);

?>
<form method="post"  role="form" id="salon-my-account-profile-form" >
    <input type="hidden" name="action" value="sln_update_profile">
    <?php wp_nonce_field('slnUpdateProfileNonce', 'slnUpdateProfileNonceField');?>
    <div class="container-fluid">
        <div class="row">
            <?php foreach (SLN_Enum_CheckoutFields::forCustomer()->appendPassword() as $key => $field): ?>
            <?php
                $type  = $field['type'];
                $width = $field['width'];
            ?>
                <div class="col-xs-12 col-md-<?php echo $width ?> <?php echo 'field-' . $key ?> <?php if ($type !== 'checkbox') {echo 'sln-input sln-input--simple';}?> <?php echo $type ? 'sln-' . $type : '' ?>">
                        <label for="<?php echo SLN_Form::makeID('sln[' . $key . ']') ?>"><?php echo esc_html__( $field['label'], 'salon-booking-system') ?></label>
                        <?php if (($key == 'phone') && ($prefix = $values['sms_prefix'] ? $values['sms_prefix'] : $plugin->getSettings()->get('sms_prefix'))): ?>
                            <div class="input-group sln-input-group">
                                    <span class="input-group-addon sln-input--addon hide"><?php echo $prefix ?></span>
                                    <?php SLN_Form::fieldText('sln[sms_prefix]', $prefix, array('type' => 'hidden')); ?>
                        <?php endif?>
                        <?php
                            if (strpos($key, 'password') === 0) {
                                SLN_Form::fieldText('sln[' . $key . ']', '', array('type' => 'password'));
                            } else if (strpos($key, 'email') === 0) {
                                SLN_Form::fieldText('sln[' . $key . ']', $values[$key], array('required' => $field->isRequired(), 'type' => 'email'));
                            } else {
                                if ($type) {
                                    $additional_opts = array(
                                        'sln[' . $key . ']', $values[$key],
                                        array('required' => $field->isRequired()),
                                    );
                                    $method_name = 'field' . ucfirst($type);
                                    if ($type === 'checkbox') {
                                        $additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
                                        $method_name     = $method_name . 'Button';
                                    }

                                    if ($type === 'select') {
					$additional_opts[2]['attrs'] = array(
					    'data-placeholder' => __('Select an option', 'salon-booking-system'),
					);
					$additional_opts[2]['empty_value'] = true;
                                        $additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1),[true]);

                                    }
                                    call_user_func_array(array('SLN_Form', $method_name), $additional_opts);
                                } else {
                                    SLN_Form::fieldText('sln[' . $key . ']', $values[$key], array('required' => $field->isRequired()));
                                }
                            }
                        ?>
                        <?php if (($key == 'phone') && $prefix): ?>
                        </div>
                        <?php endif?>
                </div>

            <?php endforeach?>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-6 sln-form-actions">
                <div class="sln-btn sln-btn--emphasis sln-btn--medium sln-btn--fullwidth">
                   <input type="submit" id="sln-accout-profile-submit" name="sln-accout-profile-submit" value="<?php _e('Update Profile','salon-booking-system');?>">
                </div>
            </div>
        </div>
	<div class="row">
	    <div class="col-xs-12 col-sm-6 sln-account--last-update">
		<?php if ($last_update): ?>
		     <?php echo sprintf(__('Last update on %s at %s', 'salon-booking-system'), $plugin->format()->date((new SLN_DateTime())->setTimestamp($last_update)), $plugin->format()->time((new SLN_DateTime())->setTimestamp($last_update))); ?>
		<?php endif; ?>
	    </div>
	</div>
    </div>
    <div class="row">
        <div class="col-xs-12">
                <div class="row" <?php    if(!$errors) echo 'style="display:none;"' ?>>
                    <div class="statusContainer col-md-12">
                        <?php if ($errors): ?>
                            <?php foreach ($errors as $error): ?>
                                <div class="sln-alert sln-alert--problem"><?php echo $error ?></div>
                            <?php endforeach ?>
                        <?php endif ?>
                    </div>
                </div>
        </div>
    </div>
</form>
