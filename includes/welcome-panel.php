<style type="text/css" id="bb-dashboard-welcome-css">
    .welcome-panel {
        padding: 0;
    }
    .welcome-panel .welcome-panel-close {
        z-index: 1;
    }
    .welcome-panel .fl-builder-content ul,
    .welcome-panel .fl-builder-content ol {
        list-style: inherit;
    }
    .welcome-panel .fl-builder-content p {
        color: inherit;
        font-size: inherit;
        margin: inherit;
        margin-bottom: 10px;
    }
</style>

<div id="bb-dashboard-welcome" class="<?php echo self::$classes; ?>">
    <?php echo do_shortcode('[fl_builder_insert_layout slug="'.self::$template[self::$current_role].'"]'); ?>
</div>

<script type="text/javascript" id="bb-dashboard-welcome-js">
    ;(function($) {
        $(document).ready(function() {
            <?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
                $('#bb-dashboard-welcome').insertBefore('#dashboard-widgets-wrap');
            <?php } ?>
        });
    })(jQuery);
</script>
