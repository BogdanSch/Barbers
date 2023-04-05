<?php if ($discountText): ?>
    <tr>
	<td align="center" valign="top" height="11" style="font-size:1px;line-height:1px;">&nbsp;</td>
    </tr>
    <tr>
	<td align="left" valign="top" style="font-size:14px;line-height:18px;color:#4d4d4d;font-weight:500;font-family: 'Avenir-Medium',sans-serif,arial;">
	    <?php echo __('Discount applied', 'salon-booking-system') ?>
	</td>
    </tr>
    <tr>
	<td align="center" valign="top" height="11" style="font-size:1px;line-height:1px;">&nbsp;</td>
    </tr>
    <tr>
	<td align="left" valign="top" style="font-size:16px;line-height:20px;color:#4d4d4d;font-weight:bold;font-family: 'Avenir-Medium',sans-serif,arial;">
	    <?php echo $discountText ?>
	</td>
    </tr>
    <tr>
	<td align="center" valign="top" height="11" style="font-size:1px;line-height:1px;">&nbsp;</td>
    </tr>
<?php endif; ?>