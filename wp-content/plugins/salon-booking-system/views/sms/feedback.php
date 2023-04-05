<?php
/**
 * @var SLN_Plugin           $plugin
 * @var SLN_Wrapper_Customer $customer
 */
$customer = $booking->getCustomer();
$custom_url = $plugin->getSettings()->get('custom_feedback_url');
$feedback_url = !empty($custom_url) ? $custom_url : home_url() . '?sln_customer_login=' . $customer->getHash() . '&feedback_id=' . $booking->getId();
$msg = $plugin->getSettings()->get('feedback_message') . "\r\n" . $feedback_url;
$msg = str_replace(array('[NAME]', '[SALON NAME]'), array($customer->getName(), $plugin->getSettings()->getSalonName()), $msg);
echo $msg;