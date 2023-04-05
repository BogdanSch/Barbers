<?php   // algolplus
/**
 * @var SLN_Plugin           $plugin
 * @var SLN_Wrapper_Customer $customer
 */
$customer = $booking->getCustomer();
$data['to']      = $customer->get('user_email');
$data['subject'] = $this->plugin->getSettings()->get('feedback_email_subject');
$manageBookingsLink = true;

$contentTemplate = '_feedback_content';

echo $plugin->loadView('mail/template', compact('booking', 'plugin', 'customer', 'data', 'manageBookingsLink', 'contentTemplate'));