<style type="text/css" id="bb-dashboard-header-css">
    .welcome-panel {
        padding: 0;
    }
    .welcome-panel .welcome-panel-close {
        z-index: 1;
    }
</style>

<div id="bb-dashboard-welcome" class="<?php echo self::$classes; ?>">
    <?php echo do_shortcode('[fl_builder_insert_layout slug="'.self::$template[self::$current_role].'"]'); ?>
</div>

<script type="text/javascript" id="bb-dashboard-header-js">
    jQuery(document).ready(function($) {
        <?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
            $('#bb-dashboard-welcome').insertBefore('#dashboard-widgets-wrap');
        <?php } ?>
        if ( $('.fl-builder-content').find('.fa').length > 0 ) {
            $('.fl-builder-content').prepend('<link rel="stylesheet" id="font-awesome-css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css" media="all">');
        }
    });
</script>
