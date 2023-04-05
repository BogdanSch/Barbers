<?php if ($attendant->getId()): ?>
    <?php $bb = SLN_Plugin::getINstance()->getBookingBuilder(); ?>
    <?php if ($service || count($services) === 1): ?>
        <?php $service = $service ? $service : current($services) ?>
        <?php if ($service->getVariablePriceEnabled()): ?>
            <div class="row sln-steps-description sln-attendant-description">
                <?php $servicePrice = $service->getVariablePrice($attendant->getId()) !== '' ? $service->getVariablePrice($attendant->getId()) : $service->getPrice() ?>
                <div class="col-xs-12 sln-service-price" data-price="<?php echo $servicePrice * $bb->getCountService($service->getId()) ?>">
                    <?php echo $plugin->format()->moneyFormatted($servicePrice);
                    echo ' ('. $plugin->format()->moneyFormatted($servicePrice*$bb->getCountService($service->getId())). ' )';
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php elseif (count($services) > 1): ?>
        <?php foreach($services as $service): ?>
            <?php if ($service->getVariablePriceEnabled()): ?>
                <div class="row sln-steps-description sln-attendant-description">
                    <?php $servicePrice = $service->getVariablePrice($attendant->getId()) !== '' ? $service->getVariablePrice($attendant->getId()) : $service->getPrice() ?>
                    <div class="col-xs-12 sln-service-price" data-price="<?php echo $servicePrice * $bb->getCountService($service->getId()) ?>">
                        <span class="sln-service-title"><?php echo $service->getTitle()?>:</span> <?php echo $plugin->format()->moneyFormatted($servicePrice);
                        echo ' ('. $plugin->format()->moneyFormatted($servicePrice*$bb->getCountService($service->getId())). ' )';?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>