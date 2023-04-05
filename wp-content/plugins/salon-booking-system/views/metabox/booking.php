<?php
/**
 * @var SLN_Metabox_Helper $helper
 * @var SLN_Plugin $plugin
 * @var SLN_Settings $settings
 * @var SLN_Wrapper_Booking $booking
 * @var string $mode
 * @var SLN_DateTime|null $date
 * @var SLN_DateTime|null $time
 */
$helper->showNonce($postType);
SLN_Action_InitScripts::enqueueCustomBookingUser();
$additional_fields = SLN_Enum_CheckoutFields::forBooking();
$checkoutFields = $additional_fields->selfClone()->required()->keys();
$customer_fields = SLN_Enum_CheckoutFields::forBookingAndCustomer()->filter('additional', true, false)->keys();

?>
<?php if (isset($_SESSION['_sln_booking_user_errors'])): ?>
    <div class="error">
    <?php foreach ($_SESSION['_sln_booking_user_errors'] as $error): ?>
        <p><?php echo $error ?></p>
    <?php endforeach?>
    </div>
    <?php unset($_SESSION['_sln_booking_user_errors']);?>
<?php endif?>

<div class="sln-bootstrap">
    <?php
do_action('sln.template.booking.metabox', $booking);

$selectedDate = !empty($date) ? $date : $booking->getDate(SLN_TimeFunc::getWpTimezone());
$selectedTime = !empty($time) ? $time : $booking->getTime(SLN_TimeFunc::getWpTimezone());

$intervalDate = clone $selectedDate;
$intervals = $plugin->getIntervals($intervalDate);

$edit_last_author = get_userdata(get_post_meta($booking->getId(), '_edit_last', true));
?>
<div class="sln-box sln-box--main sln-booking__customer <?php echo in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ? 'sln-disabled' : '' ?>">
<div class="row">
    <div class="col-xs-12 sln-row">
        <h4 class="sln-box-title--nu--sec">
            <?php _e('Customer details', 'salon-booking-system')?>
        </h4>
        <?php if (preg_match('/post\-new\.php/i', $_SERVER['REQUEST_URI'])): ?>
            <span><a target="_blanck" class="sln-booking-service--col-1 sln-customer-url--icon sln-icon--adres-cart sln-btn--icon hide"></a></span>
        <?php endif; ?>
    </div>
        <div class="col-xs-12 col-sm-6 sln-select">
            <select id="sln-update-user-field"
                 data-nomatches="<?php _e('no users found', 'salon-booking-system')?>"
                 data-placeholder="<?php _e('Start typing the name or email', 'salon-booking-system')?>"
                 class="form-control">
            </select>
        </div>
        <div class="col-xs-12 col-sm-2">
                    <button class="sln-btn sln-btn--big sln-btn--icon sln-btn--icon--left--alt sln-icon--times sln-btn--textonly sln-btn--textonly--emph" data-collection="reset">Remove</button>
            </div>
        </div>
        <div class="row"><div class="col-xs-12 col-sm-4" id="sln-update-user-message"></div></div>
        <div class="row">
        <?php
$customer = $booking->getCustomer();

if ($additional_fields) {
	foreach ($additional_fields as $key => $field) {
        if ($field['type'] === 'html') {
            continue;
        }
		$is_customer_field = $field->isCustomer();
		$value = $is_customer_field && $customer && $field->isAdditional() ? $field->getValue($customer->getId())
		: (
			in_array('_sln_booking_' . $key, get_post_custom_keys($booking->getId()) ?? array()) ? $booking->getMeta($key) : (null !== $field['default_value'] ? $field['default_value'] : '')
		);
		$method_name = 'field' . ucfirst($field['type']);
		$width = $field['width'];
		?>
                <div class="col-xs-12 col-sm-<?php
if ($width == 12) {
			echo '6';
		} else if ($width == 6) {
			echo '3';
		} else {
			echo $width;
		}
		?> sln-input--simple <?php echo 'sln-' . $field['type']; ?> sln-booking-user-field">
                    <div class="form-group sln_meta_field">
                        <label for="<?php echo $key ?>"><?php echo esc_html__($field['label'], 'salon-booking-system') ?></label>
                        <?php
$additional_opts = array($is_customer_field && $field->isAdditional() ? '_sln_' . $key :
			$helper->getFieldName($postType, $key), $value,
			array('required' => $field->isRequired()),
		);
		if ($key === 'email') {
			$additional_opts[2]['type'] = 'email';
			$additional_opts[2]['required'] = false;
		}
		if ($field['type'] === 'checkbox') {

			$additional_opts = array_merge(array_slice($additional_opts, 0, 2), array(''), array_slice($additional_opts, 2));
			$method_name = $method_name . 'Button';
		}
		if ($field['type'] === 'select') {
			$additional_opts = array_merge(array_slice($additional_opts, 0, 1), [$field->getSelectOptions()], array_slice($additional_opts, 1), [true]);
		}
        if($field['type'] === 'file'){
            $files = $booking->getMeta($key);
            if(!is_array($files)){
                $files = array($files);
            }?>
            <div class="sln_meta_field_file">
            <?php foreach($files as $file): ?>
                <?php
                    $file_url = implode('/', array_filter(array(wp_get_upload_dir()['baseurl'], trim($file['subdir'], '/'), $file['file'])));
                    $file_name = preg_replace('/^[0-9]+_/i', '', $file['file']);
                ?>
            <a href="<?php echo $file_url ?>" download="<?php echo $file_url ?>"><?php echo $file_name ?></a>
            <?php endforeach; ?>
            </div><?php
        } else {
            call_user_func_array(array('SLN_Form', $method_name), $additional_opts);
        }
		?>
                    </div>
                </div>
                <?php if ($key === 'address') {
			echo '<div class="col-xs-12"><div class="sln-separator"></div></div>';
			echo '<div class="col-xs-12">
<h5 class="sln-box-title--nu--ter">' . __('Additional informations', 'salon-booking-system') . '</h5>
</div>';
		}
	}
}?>
    <?php SLN_Form::fieldText('_sln_booking_sms_prefix', $booking->getMeta('sms_prefix') ? $booking->getMeta('sms_prefix') : $plugin->getSettings()->get('sms_prefix'), array('type' => 'hidden')); ?>

    <?php SLN_Form::fieldText('_sln_booking_default_sms_prefix', $plugin->getSettings()->get('sms_prefix'), array('type' => 'hidden')); ?>
    </div>
    <div class="row">
    	<div class="col-xs-12 col-md-6">
        <div class="sln-checkbox--nu">
            <input type="checkbox" id="_sln_booking_createuser" name="_sln_booking_createuser"/>
            <label for="_sln_booking_createuser"><?php _e('Save as new customer', 'salon-booking-system')?></label>
        </div>
        </div>
    </div>
</div>
<div id="salon-step-date"
class="sln-box sln-box--main"
      data-intervals="<?php echo esc_attr(json_encode($intervals->toArray())); ?>"
      data-isnew="<?php echo $booking->isNew() ? 1 : 0 ?>"
      data-deposit_amount="<?php echo $settings->getPaymentDepositAmount() ?>"
      data-deposit_is_fixed="<?php echo (int) $settings->isPaymentDepositFixedAmount() ?>"
      data-m_attendant_enabled="<?php echo $settings->get('m_attendant_enabled') ?>"
      data-mode="<?php echo $mode ?>"
      data-required_user_fields="<?php echo $checkoutFields->implode(',') ?>"
      data-customer_fields="<?php echo $customer_fields->implode(',') ?>">
    <div class="row form-inline">
        <div class="col-xs-12">
<h4 class="sln-box-title--nu--sec"><?php _e('Date & Time', 'salon-booking-system')?></h4>
</div>
	<?php if (!empty($edit_last_author)): ?>
	    <div class="booking-last-edit hide">
		<?php _e('Last edit', 'salon-booking-system')?>&nbsp;<span class="booking-last-edit-date"><?php echo get_the_modified_date('d.m.Y', $booking->getId()) ?></span>&nbsp;@ &nbsp;<span class="booking-last-edit-time"><?php echo get_post_modified_time('H.i', false, $booking->getId()) ?></span>&nbsp;<?php _e('by', 'salon-booking-system')?>&nbsp;<span class="booking-last-edit-author"><?php echo $edit_last_author->display_name ?></span>
	    </div>
	<?php endif;?>

	<?php if ($mode === 'sln_editor'): ?>
	    <script>
		jQuery(function () {
		    parent.jQuery('#sln-booking-editor-modal .booking-last-edit-div').html(jQuery('.booking-last-edit').html())
		});
	    </script>
	<?php endif;?>
        <div class="col-xs-12 col-sm-4 col-md-2 <?php echo in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ? 'sln-disabled' : '' ?>">
            <div class="form-group sln-input--simple">
                <label for="<?php echo SLN_Form::makeID($helper->getFieldName($postType, 'date')) ?>"><?php _e('Select a day', 'salon-booking-system')?></label>
                <?php SLN_Form::FieldJSDate(
                    $helper->getFieldName($postType, 'date'),
                    $selectedDate,
                    array(
                        'popup-class' => ($mode === 'sln_editor' ? 'off-sm-md-support' : ''),
                        'extending-classes' => (isset($_GET['action']) && $_GET['action'] == 'duplicate' ? 'cloned-data' : ''),
                    )
                ); ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-2 <?php echo in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ? 'sln-disabled' : '' ?>">
            <div class="form-group sln-input--simple">
                <label for="<?php echo SLN_Form::makeID($helper->getFieldName($postType, 'time')) ?>"><?php _e('Select an hour', 'salon-booking-system')?></label>
                <?php SLN_Form::fieldJSTime(
                    $helper->getFieldName($postType, 'time'),
                    $selectedTime,
                    array(
                        'interval' => $plugin->getSettings()->get('interval'),
                        'popup-class' => ($mode === 'sln_editor' ? 'off-sm-md-support' : ''),
                        'extending-classes' => (isset($_GET['action']) && $_GET['action'] == 'duplicate' ? 'cloned-data' : ''),
                    )
                ); ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-8 <?php echo in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ? 'sln-disabled' : '' ?>" >
            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group sln_meta_field sln-select">
                        <label><?php _e('Status', 'salon-booking-system');?></label>
                        <?php
                        SLN_Form::fieldSelect(
                            $helper->getFieldName($postType, 'status'),
                            SLN_Enum_BookingStatus::toArray(),
                            empty($_GET['post']) && SLN_Plugin::getInstance()->getSettings()->getDefaultBookingStatus() ?
                                SLN_Plugin::getInstance()->getSettings()->getDefaultBookingStatus() :
                                $booking->getStatus(),
                            array('map' => true)
                        );
                        ?>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4 sln-set-default-booking-status--block-labels <?php echo !defined("SLN_VERSION_PAY")  ? 'sln-set-default-booking-status--block-label-disabled' : '' ?>" data-default-status="<?php echo SLN_Plugin::getInstance()->getSettings()->getDefaultBookingStatus() ?>">
                    <span class="sln-booking-pro-feature-tooltip">
                        <a href="https://www.salonbookingsystem.com/homepage/plugin-pricing/?utm_source=default_status&utm_medium=free-edition-back-end&utm_campaign=unlock_feature&utm_id=GOPRO" target="_blank">
                            <?php echo __('Switch to PRO to unlock this feature', 'salon-booking-system') ?>
                        </a>
                    </span>
                    <?php if(isset($_GET['action']) && $_GET['action'] == 'duplicate'): ?>
                        <span id="sln-booking-cloned-notice"
                            class="<?php echo in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ? 'sln-disabled' : '' ?>">
                            <?php echo __('Please set a new date and time', 'salon-booking-system'); ?>
                        </span>
                    <?php else: ?>
                        <a href="#" class="sln-set-default-booking-status--label-set hide">
                            <?php _e('Set as default status', 'salon-booking-system')?>
                        </a>
                        <span class="sln-set-default-booking-status--label-done hide">
                            <?php _e('Done !', 'salon-booking-system')?>
                        </span>
                    <?php endif; ?>
                    <div class="sln-set-default-booking-status--alert-loading hide"></div>
                </div>
            </div>
        </div>
    </div>

 <div class="row form-inline">

     <div class="col-xs-12 col-md-6 col-sm-6" id="sln-notifications"  data-valid-message="<?php _e('OK! the date and time slot you selected is available', 'salon-booking-system');?>"></div>

 </div>

</div>

<?php if ($plugin->getSettings()->get('confirmation') && $booking->getStatus() == SLN_Enum_BookingStatus::PENDING): ?>
    <div class="sln_booking-topbuttons sln-box sln-box--main">
        <div class="row">
            <div class="col-xs-12 col-lg-5 col-md-5 col-sm-6 sln_accept-refuse">
                <h2><?php _e('This booking waits for confirmation!', 'salon-booking-system')?></h2>

                <div class="row">
                    <div class="col-xs-12 col-lg-5 col-md-6 col-sm-6">
                        <button id="booking-accept" class="btn btn-success"
                                data-status="<?php echo SLN_Enum_BookingStatus::CONFIRMED ?>">
                            <?php _e('Accept', 'salon-booking-system')?></button>
                    </div>
                    <div class="col-xs-12 col-lg-5 col-md-6 col-sm-6">
                        <button id="booking-refuse" class="btn btn-danger"
                                data-status="<?php echo SLN_Enum_BookingStatus::CANCELED ?>">
                            <?php _e('Refuse', 'salon-booking-system')?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="sln-box sln-box--main <?php echo in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ? 'sln-disabled' : '' ?>">
    <?php echo $plugin->loadView('metabox/_booking_services', compact('booking')); ?>
</div>
<div class="sln-box__collapsewrp <?php echo in_array(SLN_Plugin::USER_ROLE_WORKER,  wp_get_current_user()->roles) ? 'sln-disabled' : '' ?>">
    <div class="sln-box sln-box--main  sln-box--header">
        <button class="sln-btn sln-btn--big sln-btn--icon sln-btn--icon--left--alt sln-icon--arrow--up sln-btn--textonly collapsed" type="button" data-toggle="collapse" data-target="#collapseMoreDetails" aria-expanded="false" aria-controls="collapseMoreDetails">
            <?php _e('Show more details', 'salon-booking-system')?>
        </button>
    </div>
    <div class="sln-box__collapse collapse" id="collapseMoreDetails">
        <div class="sln-box sln-box--main">
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <div class="form-group sln_meta_field sln-select">
                        <label><?php _e('Duration', 'salon-booking-system');?></label>
                        <input type="text" id="sln-duration" value="<?php echo $booking->getDuration()->format('H:i') ?>" class="form-control" readonly="readonly"/>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 sln-input--simple">
                    <?php $helper->showFieldText(
                        $helper->getFieldName($postType, 'amount'),
                        apply_filters('sln.template.metabox.booking.total_amount_label', __('Amount', 'salon-booking-system') . ' (' . $settings->getCurrencySymbol() . ')', $booking),
                        $booking->getAmount()
                    );?>
                    <?php echo $booking->getTransactionId() ?
                        '<h5 class="sln-box-title--nu--ter sln-box-title--nu--dark">' . __("Transaction", 'salon-booking-system') . ': <strong>' . $booking->getTransactionId() . '</strong></h5>' :
                        '<h5 class="sln-box-title--nu--ter sln-box-title--nu--dark">' . __("Transaction", 'salon-booking-system') . ': <strong>' . __('n.a.', 'salon-booking-system') . '</strong></h5>';
                    ?>
                </div>
                <?php if ($settings->isPayEnabled()) { ?>
                    <div class="col-xs-12 col-sm-3 sln-input--simple">
                        <?php $helper->showFieldText(
                            $helper->getFieldName($postType, 'deposit'),
                            __('Deposit', 'salon-booking-system') . ' ' . SLN_Enum_PaymentDepositType::getLabel($settings->getPaymentDepositValue()) . ' (' . $settings->getCurrencySymbol() . ')',
                            $booking->getDeposit()
                        );?>
                    </div>
                <?php }?>

                <div class="col-xs-12 col-sm-3 sln-input--simple">
                    <div class="form-group sln_meta_field">
                        <label for="<?php echo $helper->getFieldName($postType, 'remainedAmount') ?>"><?php echo __('Amount to be paid', 'salon-booking-system') ?></label>
                        <?php SLN_Form::fieldText($helper->getFieldName($postType, 'remainedAmount'), $booking->getRemaingAmountAfterPay(),
                        [
                            'attrs' => [
                                'readonly' => 'readonly',
                            ],
                        ]);?>
                    </div>
                </div>

            <?php
            $plugin = SLN_Plugin::getInstance();
            $enableDiscountSystem = $plugin->getSettings()->get('enable_discount_system');
            if ($enableDiscountSystem) {
                $coupons = $plugin->getRepository(SLB_Discount_Plugin::POST_TYPE_DISCOUNT)->getAll();
                if ($coupons) {
                    $couponArr = array();
                    foreach ($coupons as $coupon) {
                        $couponArr[$coupon->getId()] = $coupon->getTitle();
                    }
                    $discount_helper = new SLB_Discount_Helper_Booking();

                    $discounts = $discount_helper->getBookingDiscountIds($booking);

                    $tmpCoupons = array();

                    foreach ($discounts as $discountID) {
                        if (!empty($couponArr[$discountID])) {
                            $tmpCoupons[$discountID] = $couponArr[$discountID];
                            unset($couponArr[$discountID]);
                        }
                    }

                    $couponArr = $tmpCoupons + $couponArr;

                    ?>
                    <div class="col-xs-12 col-sm-3">
                        <div class="form-group sln_meta_field sln-select sln-select2-selection__search-primary">
                            <label><?php _e('Discount', 'salon-booking-system');?></label>
                            <?php SLN_Form::fieldSelect(
                                $helper->getFieldName($postType, 'discounts[]'),
                                $couponArr,
                                $discount_helper->getBookingDiscountIds($booking),
                                array(
                                    'map' => true,
                                    'empty_value' => 'No Discounts',
                                )
                            );?>
                            <span class="help-block" style="display: none"><?php printf(__('Please click on "%s" button to see the updated prices', 'salon-booking-system'), __("Update booking", 'salon-booking-system'));?></span>
                        </div>
                    </div>
            <?php }}?>
            <div class="col-xs-12">
                <button class="sln-btn sln-btn--main sln-btn--big sln-btn--icon sln-icon--settings" id="calculate-total"><?php _e('Calculate total', 'salon-booking-system')?></button>
                <span class="sln-calc-total-loading"></span>
            </div>

            <?php do_action('sln.template.metabox.booking.total_amount_row', $booking);?>
        </div>
    </div>
    <div class="sln-box sln-box--main sln_booking-details__notes">
        <h4 class="sln-box-title--nu--sec">
                <?php _e('Notes', 'salon-booking-system')?>
            </h4>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group sln_meta_field sln-input--simple">
                    <label><?php _e('Personal message', 'salon-booking-system');?></label>
                    <?php SLN_Form::fieldTextarea(
                        $helper->getFieldName($postType, 'note'),
                        $booking->getNote()
                    );?>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group sln_meta_field sln-input--simple">
                    <label><?php _e('Administration notes', 'salon-booking-system');?></label>
                    <?php SLN_Form::fieldTextarea(
                        $helper->getFieldName($postType, 'admin_note'),
                        $booking->getAdminNote()
                    );?>
                </div>
            </div>
        </div>
        <!-- collapse END -->
    </div>
    <!-- collapse wrapper END -->
</div>
<?php if (isset($_GET['sln_editor_popup'])): ?>
    <script>
        jQuery(document).ready(function () {

            jQuery('.sln-last-edit').html(jQuery('.booking-last-edit').html())

            jQuery("[data-action=save-edited-booking]").on("click", function () {
                if (sln_validateBooking()) {
                    jQuery("#save-post").trigger("click");
                }
            });

            jQuery("[data-action=delete-edited-booking]").on("click", function () {
                if (sln_validateBooking()) {
                    var href = jQuery(".submitdelete").attr("href");
                    jQuery.get(href).success(function () {
                        window.close();
                    });
                }
            });

            jQuery("[data-action=duplicate-edited-booking]").on("click", function () {

                if (jQuery(this).closest('.sln-duplicate-booking--disabled').length > 0) {
                    return false;
                }

                if (sln_validateBooking()) {
                    var href = '<?php echo admin_url('/post-new.php?post_type=sln_booking&action=duplicate&post=%id&mode=sln_editor&sln_editor_popup=1') ?>';
                    href = href.replace('%id', jQuery('#post_ID').val());
                    window.location.href = href;
                }
            });
        })
    </script>
    <div class="sln-editor-popup-actions pull-right">
        <div class="sln-last-edit"></div>
        <div class="sln-editor-popup-actions-list">
            <button type="button" class="sln-btn sln-btn--nu sln-btn--nu--highemph sln-btn--big" aria-hidden="true" data-action="save-edited-booking">
                <?php _e('Save', 'salon-booking-system')?>
            </button>
            <div class="sln-duplicate-booking <?php echo !defined("SLN_VERSION_PAY")  ? 'sln-duplicate-booking--disabled' : '' ?> <?php echo isset($_GET['action']) && $_GET['action'] === 'duplicate' ? 'hide' : '' ?>">
                <span class="sln-booking-pro-feature-tooltip">
                    <a href="https://www.salonbookingsystem.com/homepage/plugin-pricing/?utm_source=default_status&utm_medium=free-edition-back-end&utm_campaign=unlock_feature&utm_id=GOPRO" target="_blank">
                        <?php echo __('Switch to PRO to unlock this feature', 'salon-booking-system') ?>
                    </a>
                </span>
                <button type="button" class="sln-btn sln-btn--nu sln-btn--nu--lowhemph sln-btn--big" aria-hidden="true" data-action="duplicate-edited-booking"><?php _e('Duplicate', 'salon-booking-system')?></button>
            </div>
            <button type="button" class="sln-btn sln-btn--nu sln-btn--nu--lowhemph sln-btn--big" aria-hidden="true" data-action="delete-edited-booking">
                <?php _e('Delete', 'salon-booking-system')?>
            </button>
        </div>
    </div>
<?php endif; ?>
</div>
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
