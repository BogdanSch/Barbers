<style type="text/css">
    #post-preview, #view-post-btn,
    #misc-publishing-actions #visibility,
    #major-publishing-actions,
    #post-body-content {
        display: none;
    }
</style>

<script type="text/javascript">
    jQuery(function () {
        jQuery('#_sln_booking_status, #post_status').on('change', function () {
            jQuery('#_sln_booking_status, #post_status').val(jQuery(this).val());
        });
        <?php if('duplicate' == $_GET['action']): ?>
            jQuery(document).ready(function(){
                console.log(<?php echo get_post($_GET['post'])->post_author ?>);
                jQuery('#post_author').val(<?php echo get_post($_GET['post'])->post_author ?>);
            })
        <?php endif; ?>
    });
</script>