<?php
include $this->plugin->getViewFile('admin/utilities/settings_inpage_navbar');
sum(
	// link anchor, link text
	array('#sln-prices_visibility', __('Prices visibility', 'salon-booking-system')),
	array('#sln-currency', __('Currency', 'salon-booking-system'))
);
?>
<div id="sln-currency" class="sln-box sln-box--main">
        <h2 class="sln-box-title"><?php _e('Currency', 'salon-booking-system');?></h2>
        <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4 form-group sln-select ">
                    <label for="salon_settings_pay_currency"><?php _e('Set your currency', 'salon-booking-system')?></label>
                    <?php echo SLN_Form::fieldCurrency(
	"salon_settings[pay_currency]",
	$this->settings->getCurrency()
) ?>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 form-group sln-select ">
                    <label for="salon_settings_pay_currency_pos"><?php _e('Set your currency position', 'salon-booking-system')?></label>
                     <?php echo SLN_Form::fieldSelect(
	'salon_settings[pay_currency_pos]',
	array('left' => __('on left side', 'salon-booking-system'), 'right' => __('on right side', 'salon-booking-system')),
	$this->settings->get('pay_currency_pos'),
	array(),
	true
) ?>
                </div>
            <div class="col-xs-12 col-sm-6 col-md-4 visible-lg-block sln-box-maininfo">
                <p class="sln-box-info"><?php _e('If you want a new currency to be added please send us an email to support@wpchef.it', 'salon-booking-system');?></p>
            </div>
            <div class="clearfix visible-lg-block"></div>
            <div class="col-xs-6 col-sm-3 col-md-2 sln-input--simple">
                <?php $this->row_input_text('pay_decimal_separator', __('Decimal separator', 'salon-booking-system'));?>
            </div>
            <div class="col-xs-6 col-sm-3 col-md-2 sln-input--simple sln-pay-thousand-separator-option">
                <?php $this->row_input_text('pay_thousand_separator', __('Thousand separator', 'salon-booking-system'));?>
            </div>
            <?php /* this box is a carbon copy of the one some lines above, this one is visible on smaller screens, the other one on large screens. They must have the same content. */?>
            <div class="col-xs-12 col-sm-6 col-md-4 hidden-lg sln-box-maininfo">
                <p class="sln-box-info"><?php _e('If you want a new currency to be added please send us an email to support@wpchef.it', 'salon-booking-system');?></p>
            </div>
                </div>

        <div class="row">

        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6">
            <div id="sln-prices_visibility" class="sln-box sln-box--main sln-box--main--small">
                <h2 class="sln-box-title"><?php _e('Prices visibility', 'salon-booking-system')?></h2>
                <div class="row">
                    <div class="col-xs-12 form-group sln-checkbox">
                        <?php $this->row_input_checkbox('hide_prices', __('Hide Prices', 'salon-booking-system'));?>

                    <div class="sln-box-maininfo">
                        <p class="sln-box-info"><?php _e('Select this Option if you want to hide all prices from the front end.<br/>Note: Online Payment will be disabled.', 'salon-booking-system')?></p>
                    </div>
                    </div>
                </div>
            </div>
        </div>

<div class="col-xs-12 col-sm-6 col-md-6">
    <div id="" class="sln-box sln-box--main sln-box--main--small">
        
        <div class="sln-staff-member-assigned-bookings-only--alert">
            <p>
                <?php _e('Accept online payments with PayPal, Stripe or many other payments platforms.', 'salon-booking-system') ?>
                <br/>
                <?php _e('Request upfront deposit or offer a "Pay later" option.', 'salon-booking-system') ?>
                <br/>
                <?php _e('Switch to PRO version today, and get access to all available features.', 'salon-booking-system') ?>
                <br/>
                <br/>
                <a href="https://www.salonbookingsystem.com/homepage/plugin-pricing/?utm_source=Payments%20settings&utm_medium=Plugin&utm_campaign=Go%20PRO" target="_blank">

                    <?php _e('OK, I need this..', 'salon-booking-system')?>
                </a>
            </p>
        </div>
    </div>
</div>

    </div>
