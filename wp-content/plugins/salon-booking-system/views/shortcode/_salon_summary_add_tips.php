<?php
/**
 * @var SLN_Plugin $plugin
 * @var int $size
 */
?>

<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_LARGE) ? '<div class="row">' : ''); ?>
<div class="col-xs-12 sln-summary__tips">
    <div class="row">
	<div class="col-xs-12 sln-input sln-input--simple sln-input--lon">
	    <?php
	    $args = array(
		    'label'        => __('Leave a tip', 'salon-booking-system'),
		    'tag'          => 'label',
		    'textClasses'  => '',
		    'inputClasses' => '',
		    'tagClasses'   => '',
	    );
	    echo $plugin->loadView('shortcode/_editable_snippet', $args);
	    ?>
	</div>
	<div class="col-xs-12 col-sm-6 sln-input sln-input--simple">
	    <?php SLN_Form::fieldText(
		    'sln[tips]',
		    '',
		    array('attrs' => array('placeholder' => __('key in the desired amount', 'salon-booking-system')))
	    ); ?>
	</div>
	<div class="col-xs-12 col-sm-6">
	    <div class="sln-btn sln-btn--emphasis sln-btn--medium sln-btn--fullwidth">
		<button data-salon-toggle="tips" id="sln_tips_btn" type="button" onclick="sln_applyTipsAmount();">
		    <?php _e('Apply', 'salon-booking-system'); ?>
		</button>
	    </div>
	</div>
	<div class="col-xs-12">
	    <div id="sln_tips_status"></div>
	</div>
    </div>
</div>
<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_LARGE) ? '</div>' : ''); ?>