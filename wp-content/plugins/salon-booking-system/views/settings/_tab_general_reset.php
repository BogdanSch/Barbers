<?php
/**
 * @var $helper SLN_Admin_Settings
 */
?>
<div id="sln-salon_booking_system_reset_all_settings" class="sln-box sln-box--main sln-box--haspanel">
    <h2 class="sln-box-title sln-box__paneltitle"><?php _e( 'Reset all settings', 'salon-booking-system' ); ?></h2>
    <div class="collapse sln-box__panelcollapse">
        <div class="row">
            <div class="sln-btn sln-btn--main sln-btn--big sln-reset-settings">
                <input type="submit" name="reset" id="reset" class="sln-reset-settings-button" value="Reset Settings" onClick="return confirm('<?php echo esc_js( __('Do you really want to reset?', 'salon-booking-system' ) ); ?>');">
            </div>
        </div>
    </div>
</div>