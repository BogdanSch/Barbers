<?php
if(!$data['comments']) return;
$plugin = SLN_Plugin::getInstance();
?>
<section class="sln-datashortcode sln-datashortcode--comments">
    <div class="sln-datalist sln-datalist--styled sln-datalist--1cols">
	<?php
	foreach ($data['comments'] as $comment) {
        if($comment->comment_approved) {
        ?>
                <div class="sln-datalist__item">
                    <div class="sln-datalist__item__author">
                            <?php $user = get_user_by('email', $comment->comment_author_email) ?>
                            <?php echo $user ? $user->first_name . ' '. $user->last_name : $comment->comment_author ?>
                        </div>
                        <p class="sln-datalist__item__date">
                            <?php echo date('d.m.Y', strtotime($comment->comment_date)) ?>
			</p>
                        <?php if ($comment->rating) { ?>
                            <span class="sln-datalist__item__rating">
                                <input type="hidden" name="sln-rating" value="<?php echo $comment->rating; ?>">
                                <span class="rating"></span>
                                <span class="rating-value"><?php echo $comment->rating ?>/5</span>
                            </span>
                        <?php } ?>
                        <p class="sln-datalist__item__comment">
                            <?php echo $comment->comment_content ?>
			</p>
		</div>
	<?php }
    } ?>
		<div class="sln-datalist_clearfix"></div>
	</div>
</section>
