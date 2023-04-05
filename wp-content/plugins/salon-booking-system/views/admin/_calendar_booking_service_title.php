<?php
/**
 * @var SLN_Wrapper_Booking_Service $bookingService
 * @var SLN_Wrapper_Booking $booking
 */
$format = SLN_Plugin::getInstance()->format();
?><strong><?php echo esc_attr($booking->getDisplayName()) . '<br /> ' . $format->time($booking->getStartsAt()) . ' &#8594; ' . $format->time($booking->getEndsAt()) ?><br /><?php $comments = get_comments("post_id=" . $booking->getId() . "&type=sln_review"); echo (isset($comments[0]) ? $comments[0]->comment_content .'<br />' : ''); ?></strong>

<?php echo ( $bookingService->getService() ? $bookingService->getService()->getName() : '' ). '<br /><span>' .
    (($attendant = $bookingService->getAttendant()) ?
        (!is_array($attendant) ?
            esc_attr($attendant->getName()) :
            esc_attr(SLN_Wrapper_Attendant::implodeArrayAttendantsName(' ', $attendant))) .
        '&nbsp;' :
        '') .
    $format->time($bookingService->getStartsAt()) . ' &#8594; ' .
    $format->time($bookingService->getEndsAt()) . '<br /></span>'; ?>