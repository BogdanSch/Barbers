<?php
/**
 * @var SLN_Plugin $plugin
 * @var SLN_Settings $settings
 * @var int $size
 * @var float $tipsValue
 */

$taxValue = $bb->getTaxFromTotal();
if('exclusive' == $settings->get('enter_tax_price')):
?>
<div class="row sln-summary-row sln-summary-row--amount-exclude-tax">
	<div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-6 col-md-6'); ?> sln-data-desc">
		<?php
		$args = array(
			'label'        => __('Total amount ( tax excluded )', 'salon-booking-system'),
			'tag'          => 'span',
			'textClasses'  => 'text-min label',
			'inputClasses' => 'input-min',
			'tagClasses'   => 'label',
		);
		echo $plugin->loadView('shortcode/_editable_snippet', $args);
		?>
	</div>
    <div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-6 col-md-6'); ?> sln-data-val">
		<span id="sln_amount_exclude_tax_value"><?php echo $plugin->format()->money($bb->getTotal() - $taxValue, false, false, true); ?></span>
	</div>
	<div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-12 col-md-12'); ?>"><hr></div>
</div>
<?php endif; ?>
<div class="row sln-summary-row sln-summary-row--tax">
	<div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-6 col-md-6'); ?> sln-data-desc">
		<?php
		$args = array(
			'label'        => __('TAX', 'salon-booking-system') . '(' . $settings->get('tax_value') . '%)',
			'tag'          => 'span',
			'textClasses'  => 'text-min label',
			'inputClasses' => 'input-min',
			'tagClasses'   => 'label',
		);
		echo $plugin->loadView('shortcode/_editable_snippet', $args);
		?>
	</div>
    <div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-6 col-md-6'); ?> sln-data-val">
		<span id="sln_tax_value"><?php echo $plugin->format()->money($taxValue, false, false, true); ?></span>
	</div>
	<div class="<?php echo ($size === SLN_Enum_ShortcodeStyle::getSize(SLN_Enum_ShortcodeStyle::_SMALL) ? 'col-xs-12' : 'col-sm-12 col-md-12'); ?>"><hr></div>
</div>