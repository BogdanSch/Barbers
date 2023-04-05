<!--booking-main start-->
<div class="booking-main" data-attrs="<?php echo esc_attr(json_encode($data['attrs'])); ?>">
    <div class="booking-in">
	<div class="booking-calender">
	    <div class="booking-calendermain mobile-hide">
		<div class="calender-head">
		    <div class="calender-head-in">
			<ul>
			    <li class="first-column">
				<div class="katrine">&nbsp;</div>
			    </li>
			    <?php $i = 0;?>
			    <?php foreach ($data['attendants'] as $att): ?>
				<?php $i++;?>
				<li class="column-<?php echo $i ?> header-column">
				    <div class="katrine">
					<div class="figure-left">&nbsp;</div>
					<figure>
					    <?php if (!empty($att['img'])): ?>
						<img src="<?php echo $att['img'] ?>" width="81" height="81" alt="img" class="img">
					    <?php endif;?>
					</figure>
					<h5>
					    <?php echo $att['name'] ?>
					</h5>
				    </div>
				</li>
			    <?php endforeach?>
			</ul>
			<div class="clear"></div>
		    </div>
		</div>
		<div class="calender-content">
		    <?php foreach ($data['dates'] as $i => $datetime): ?>
			<?php $date = $datetime->format('Y-m-d')?>
			<div class="calender-head-in calender-head-in-content">
			    <ul>
				<li class="black-line first-column <?php echo ($i >= count($data['dates']) - 1) ? 'no-border' : '' ?>">
				    <div class="katrine katrine1 katrine6">
					<div class="katrine-desktop">
					    <h6>
						<?php echo SLN_TimeFunc::translateDate('l', $datetime->getTimestamp(), $datetime->getTimezone()) ?>
					    </h6>
					    <em>
					    	<span class="date--long">
						    <?php echo SLN_TimeFunc::translateDate('d F Y', $datetime->getTimestamp(), $datetime->getTimezone()) ?>
					    	</span>
					    	<span class="date--short">
						    <?php echo SLN_TimeFunc::translateDate('d M Y', $datetime->getTimestamp(), $datetime->getTimezone()) ?>
					    	</span>
					    </em>
					</div>
				    </div>
				</li>
				<?php $j = 0;?>
				<?php foreach ($data['attendants'] as $att): ?>
				    <?php $j++;?>
				    <li class="column-<?php echo $j ?> <?php echo ($i >= count($data['dates']) - 1) ? 'no-border' : '' ?>">
					<div class="katrine katrine1 katrine2 katrine6">
					    <ul>
						<?php if (!empty($att['events'][$date])): ?>
						    <?php foreach ($att['events'][$date] as $event): ?>
							<li>
							    <p>
								<small>
								    <?php echo $event['time'] ?>
								</small>
								<em>
								    <?php echo $event['title'] ?>
								</em>
							    </p>
							    <div class="tool-tip tool-tip-mobile">
								<div class="tool-tip-arrow">
								   <div class="arrow">
								      <div class="outer"></div>
								      <div class="inner"></div>
								   </div>
								   <div class="tooltip-in">
								      <?php foreach ($event['services'] as $s): ?>
									    <p><?php echo $s ?></p>
									<?php endforeach;?>
									<a href="#"><?php echo $event['status'] ?></a>
								   </div>
								</div>
							     </div>
							</li>
						    <?php endforeach?>
						<?php endif?>
					    </ul>
					    <div class="clear"></div>
					</div>
				    </li>
				<?php endforeach?>
			    </ul>
			    <div class="clear"></div>
			</div>
		    <?php endforeach?>
		</div>
	    </div>
	</div>
    </div>
</div>
<!--booking-main end-->
<!--mobile page start-->
<div class="booking-calendermain booking-calendermain-mobile">
    <div class="calender-head-in-content">
	<ul class="mobile-page1"> </ul>
    </div>
    <?php $i = 0;?>
    <?php foreach ($data['attendants'] as $att): ?>
	<?php $i++;?>
	<div class="calender-head-in-content">
	    <ul class="mobile-page2">
		<li class="column-<?php echo $i ?> header-column">
		    <div class="katrine">
			<div class="figure-left">&nbsp;</div>
			<figure>
			    <?php if (!empty($att['img'])): ?>
				<img src="<?php echo $att['img'] ?>" width="81" height="81" alt="img" class="img">
			    <?php endif;?>
			</figure>
			<h5>
			    <?php echo $att['name'] ?>
			</h5>
		    </div>
		</li>
		<?php foreach ($data['dates'] as $datetime): ?>
		    <?php $date = $datetime->format('Y-m-d')?>
		    <li class="column-<?php echo $i ?>">
			<div class="katrine katrine1 katrine2 katrine6">
			    <div class="katrine-mobile">
				<div class="katrine-mobile-cnt">
				    <h6>
					<?php echo SLN_TimeFunc::translateDate('l', $datetime->getTimestamp(), $datetime->getTimezone()) ?>
				    </h6>
				    <em>
					<span class="date--long">
					    <?php echo SLN_TimeFunc::translateDate('d F Y', $datetime->getTimestamp(), $datetime->getTimezone()) ?>
					</span>
					<span class="date--short">
					    <?php echo SLN_TimeFunc::translateDate('d M Y', $datetime->getTimestamp(), $datetime->getTimezone()) ?>
					</span>
				    </em>
				</div>
			    </div>
			    <ul>
				<?php if (!empty($att['events'][$date])): ?>
				    <?php foreach ($att['events'][$date] as $event): ?>
					<li>
					    <p>
						<small>
						    <?php echo $event['time'] ?>
						</small>
						<em>
						    <?php echo $event['title'] ?>
						</em>
					    </p>
					    <div class="tool-tip tool-tip-mobile">
						<div class="tool-tip-arrow">
						   <div class="arrow">
						      <div class="outer"></div>
						      <div class="inner"></div>
						   </div>
						   <div class="tooltip-in">
						      <?php foreach ($event['services'] as $s): ?>
							    <p><?php echo $s ?></p>
							<?php endforeach;?>
							<a href="#"><?php echo $event['status'] ?></a>
						   </div>
						</div>
					     </div>
					</li>
				    <?php endforeach?>
				<?php endif?>
			    </ul>
			    <div class="clear"></div>
			</div>
		    </li>
		<?php endforeach?>
	    </ul>
	</div>
    <?php endforeach?>
</div>
<!--mobile page end-->