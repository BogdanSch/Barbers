<?php
/** @var SLN_Wrapper_Booking $booking */
$format = SLN_Plugin::getInstance()->format();
?><strong><?php echo esc_attr($booking->getDisplayName()) . '<br /> ' . $format->time($booking->getStartsAt()) . ' &#8594; ' . $format->time($booking->getEndsAt()) ?><br /><?php $comments = get_comments("post_id=" . $booking->getId() . "&type=sln_review"); echo (isset($comments[0]) ? $comments[0]->comment_content .'<br />' : ''); ?></strong>

<?php foreach($booking->getBookingServices()->getItems() as $bookingService): ?>
    <br>
    <?php
    echo $bookingService->getService()->getName() .'<br /><span>'.
         (($attendant = $bookingService->getAttendant()) ?
            (!is_array($attendant) ?
                esc_attr($attendant->getName()) :
                SLN_Wrapper_Attendant::implodeArrayAttendantsName(' ', $attendant))
            .'&nbsp;' :
            '').
         $format->time($bookingService->getStartsAt()) . ' &#8594; ' .
         $format->time($bookingService->getEndsAt()).'<br /></span>';


    ?>
<?php endforeach ?>

<?php if ($booking->getNote()): ?>
<br/>
<?php echo $booking->getNote() ?>
<?php endif ?>

<?php if ($booking->getAdminNote()): ?>
<br/>
<?php echo $booking->getAdminNote() ?>
<?php endif ?>