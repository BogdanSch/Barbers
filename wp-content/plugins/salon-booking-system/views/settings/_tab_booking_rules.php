<?php
/**
 * @var $plugin SLN_Plugin
 * @var $availabilities array
 */

$label = __('On-line booking available days', 'salon-booking-system');
$block = __(
	'Create one or more rules to limit online reservation to specific days and time range. <br />Leave blank if you want bookings available everydays at every hour',
	'salon-booking-system'
);
if (!is_array($availabilities)) {
	$availabilities = array();
}
SLN_Action_InitScripts::enqueueCustomSliderRange();
?>
<div id="sln-online_booking_available_days" class="sln-box sln-box--main sln-booking-rules  sln-box--haspanel">
        <h2 class="sln-box-title sln-box__paneltitle"><?php echo $label ?>
            <span class="block"><?php echo $block ?></span></h2>
             <div class="collapse sln-box__panelcollapse">
	<div class="row">
    <div class="sln-booking-rules-wrapper">
	<?php $n = 0;?>
        <?php foreach ($availabilities as $row): $n++;?>
									            <?php echo $plugin->loadView(
		'settings/_availability_row',
		array(
			'prefix' => $base . "[$n]",
			'row' => $row,
			'rulenumber' => $n,
			'show_specific_dates' => isset($show_specific_dates) ? $show_specific_dates : false,
		)
	); ?>
									        <?php endforeach?>
    </div>
    <div class="col-xs-12 sln-box__actions">
        <button data-collection="addnew"
                class="sln-btn sln-btn--main sln-btn--big sln-btn--icon sln-icon--file"><?php _e(
	'Add new booking rule',
	'salon-booking-system'
)?>
        </button>
    </div>
    <div data-collection="prototype" data-count="<?php echo count($availabilities) ?>">
        <?php echo $plugin->loadView(
	'settings/_availability_row',
	array(
		'row' => array(),
		'rulenumber' => '__new__',
		'prefix' => $base . "[__new__]",
                'show_specific_dates' => isset($show_specific_dates) ? $show_specific_dates : false,
	)
); ?>
    </div>
</div>
</div>
</div>
