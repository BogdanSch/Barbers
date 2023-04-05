<?php

$settings = SLN_Plugin::getInstance()->getSettings();
$incorrectLastStepNote = 'You will receive a booking confirmation by email.If you do not receive an email in 5 minutes, check your Junk Mail or Spam Folder. If you need to change your reservation, please call <strong>[SALON PHONE]</strong> or send an e-mail to <strong>[SALON PHONE]</strong>.';
$currentLastStepNote = $settings->get('last_step_note');
$defaultData = require SLN_PLUGIN_DIR . '/_install_data.php';

if ($currentLastStepNote === $incorrectLastStepNote) {
    $settings->set('last_step_note', $defaultData['settings']['last_step_note']);
    $settings->save();
}
