<?php
/**
 * @var string               $content
 * @var SLN_Shortcode_Salon $salon
 */

$style = $salon->getStyleShortcode();
$cce = !$plugin->getSettings()->isCustomColorsEnabled();
$class = SLN_Enum_ShortcodeStyle::getClass($style);
?>
<div id="sln-salon" class="sln-bootstrap container-fluid <?php
            echo $class;
            if(!$cce) {
              echo ' sln-customcolors';
            }
            echo ' sln-step-' . $salon->getCurrentStep(); ?>">
    <?php

    $args = array(
        'label'        => __('Book an appointment', 'salon-booking-system'),
        'tag'          => 'h2',
        'textClasses'  => 'sln-salon-title',
        'inputClasses' => '',
        'tagClasses'   => 'sln-salon-title',
    );
    echo $plugin->loadView('shortcode/_editable_snippet', $args);
    do_action('sln.booking.salon.before_content', $salon, $content);
    echo $content;
    ?>
<div id="sln-notifications"></div>
</div>
<?php if(defined('SLN_SPECIAL_EDITION') && SLN_SPECIAL_EDITION && !isset($_POST['sln'])): ?>
<div id="sln-plugin-credits"><?php _e('Proudly powered by', 'salon-booking-system') ?> <a target="_blanck" href="https://www.salonbookingsystem.com/plugin-pricing/#utm_source=plugin-credits&utm_medium=booking-form&utm_campaign=booking-form&utm_id=plugin-credits"><?php _e('Salon Booking System', 'salon-booking-system'); ?></a></div>
<?php endif; ?>
