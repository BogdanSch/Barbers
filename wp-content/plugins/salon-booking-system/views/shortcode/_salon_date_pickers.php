<?php
/**
 * @var $date DateTime
 * @var $plugin SLN_Plugin
 * @var SLN_Shortcode_Salon_DateStep $step
 */
$style = $step->getShortcode()->getStyleShortcode();
$size = SLN_Enum_ShortcodeStyle::getSize($style);
?>
<?php ob_start();?>
<?php SLN_Form::fieldJSDate('sln[date]', $date, array('inline' => true))?>
<input name="sln[date]" type="hidden" value="<?php echo SLN_plugin::getInstance()->format()->date($date) ?>"/>
<?php $datepicker = ob_get_clean();
ob_start();?>
<div id="sln_timepicker_viewdate"></div>
<?php SLN_Form::fieldJSTime('sln[time]', $date, array('interval' => $plugin->getSettings()->get('interval'), 'inline' => true))?>
<input name="sln[time]" type="hidden" value="<?php echo SLN_plugin::getInstance()->format()->time($date) ?>"/>
<?php $timepicker = ob_get_clean();?>

<?php if ($size == '900'): ?>
    <div class="row sln-box--main">
        <div class="col-xs-12 col-md-4 sln-input sln-input--datepicker">
            <?php echo $datepicker ?>
        </div>
        <div class="col-xs-12 col-md-4 sln-input sln-input--datepicker">
            <?php echo $timepicker ?>
        </div>
        <div class="col-xs-12 col-md-4 sln-input sln-box--formactions">
            <label class="hidden-xs hidden-sm" for="">&nbsp;</label>
            <?php include "_form_actions.php"?>
        </div>
    </div>
<?php elseif ($size == '600'): ?>
    <div class="row sln-box--main">
        <div class="col-xs-12 sln-input sln-input--datepicker">
            <?php echo $datepicker ?>

        </div>
        <div class="col-xs-12 sln-input sln-input--datepicker">
            <?php echo $timepicker ?>
        </div>
    </div>
    <div class="row sln-box--main sln-box--formactions">
        <div class="col-xs-12"><?php include "_form_actions.php"?></div>
    </div>
<?php elseif ($size == '400'): ?>
    <div class="row sln-box--main">
        <div class="col-xs-12 sln-input sln-input--datepicker">
            <?php echo $datepicker ?>
        </div>
        <div class="col-xs-12 sln-input sln-input--datepicker">
            <?php echo $timepicker ?>
        </div>
    </div>
    <div class="row sln-box--main sln-box--formactions">
        <div class="col-xs-12"><?php include "_form_actions.php"?></div>
    </div>
<?php else: ?>
    <?php throw new Exception('size not managed')?>
<?php endif?>