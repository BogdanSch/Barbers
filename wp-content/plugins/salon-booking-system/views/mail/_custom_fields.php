 <?php

    $additional_fields = SLN_Enum_CheckoutFields::additional();

    $_additional_fields = array();

    $customer = $booking->getCustomer();

    foreach ($additional_fields as $key => $field) {

	$value = $field->isCustomer() && $customer  ?  $field->getValue($customer->getId()) : (
                !is_null($booking->getMeta($key))? $booking->getMeta($key)  : ( null !== $field['default_value']  ? $field['default_value'] : '')
            );

	if($field->isHidden() || empty($value) ) {
	    continue;
	}

	$_additional_fields[] = array(
	    'label' => esc_html__( $field['label'], 'salon-booking-system'),
	    'value' => $value,
		'type' => $field['type'],
	);
    }

?>

<?php if($_additional_fields): ?>
    <table width="198" cellspacing="0" cellpadding="0" border="0" class="width">
	<?php foreach ($_additional_fields as $field): ?>
		<?php if($field['type'] === 'file'){ continue; }?>
	    <tr>
		<td align="center" valign="top" height="54" style="font-size:1px;line-height:1px;" class="height0">&nbsp;</td>
	    </tr>
	    <tr>
		<td align="left" valign="top" style="font-size:14px;line-height:17px;color:#4d4d4d;font-weight:500;font-family: 'Avenir-Medium',sans-serif,arial;">
		    <?php echo esc_html__( $field['label'], 'salon-booking-system') ?>
		</td>
	    </tr>
	    <tr>
		<td align="left" valign="top" style="font-size:14px;line-height:20px;color:#4d4d4d;font-weight:bold;font-family: 'Avenir-Medium',sans-serif,arial;">
		    <?php echo $field['value']; ?>
		</td>
	    </tr>
	<?php endforeach; ?>
    </table>
<?php endif; ?>
