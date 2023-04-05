<?php

    $nb_message = $plugin->getSettings()->get('new_booking_message');

    $nb_message = str_replace(
	array('[DATE]', '[TIME]', '[NAME]', '[SALON NAME]', '\\\\r\\\\n', '\\r\\n', '\\\\n', '\\n'), array(
	    $plugin->format()->date($booking->getDate()),
        $plugin->format()->time($booking->getTime()),
	    $booking->getDisplayName(),
	    $plugin->getSettings()->get('gen_name') ? $plugin->getSettings()->get('gen_name') : get_bloginfo('name'),
	    '<br/>',
	    '<br/>',
	    '<br/>',
	    '<br/>'
	),
	nl2br($nb_message)
    );
?>

<tr>
    <td align="left" valign="top" style="font-size:16px;line-height:24px;color:#4d4d4d;font-family: 'Avenir-Medium',sans-serif,arial;padding: 10px 0 20px 18px;">
	<?php echo $nb_message . '' ?>
    </td>
</tr>
<tr>
    <td align="center" valign="top" height="22" style="font-size:1px;line-height:1px;">&nbsp;</td>
</tr>
<tr>
    <td align="left" valign="top" style="font-size:18px;line-height:29px;color:#4d4d4d;font-weight:500;font-family: 'Avenir-Medium',sans-serif,arial;padding: 0 0 0 8px;" class="font1">
	<?php if ( $plugin->getSettings()->get('confirmation') && $booking->hasStatus(SLN_Enum_BookingStatus::PENDING) ) : ?>
	    <?php echo __('Your booking is pending, please await our confirmation.','salon-booking-system') ?>
	<?php endif ?>
    </td>
</tr>
<tr>
    <td align="center" valign="top" height="22" style="font-size:1px;line-height:1px;">&nbsp;</td>
</tr>