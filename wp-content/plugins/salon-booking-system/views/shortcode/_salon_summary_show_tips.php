<?php
/**
 * @var SLN_Plugin $plugin
 * @var int $size
 * @var float $tipsValue
 */
?>
<div class="row sln-summary-row sln-summary-row--tips">
	<div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-6 col-md-6'); ?> sln-data-desc">
		<?php
		$args = array(
			'label'        => __('Tips', 'salon-booking-system'),
			'tag'          => 'span',
			'textClasses'  => 'text-min label',
			'inputClasses' => 'input-min',
			'tagClasses'   => 'label',
		);
		echo $plugin->loadView('shortcode/_editable_snippet', $args);
		?>
	</div>
	<div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-6 col-md-6'); ?> sln-data-val">
		<span id="sln_tips_value"><?php echo $plugin->format()->money($tipsValue, false, false, true); ?></span>
	</div>
	<div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-12 col-md-12'); ?>"><hr></div>
</div>