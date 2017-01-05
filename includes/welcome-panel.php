<style type="text/css" id="bb-dashboard-welcome-css">
    .welcome-panel {
        padding: 0;
    }
    .welcome-panel .welcome-panel-close {
        z-index: 1;
        <?php if ( 'no' == self::$dismissible[self::$current_role] ) { ?>
            display: none;
        <?php } ?>
    }
    #bb-dashboard-welcome {
        -webkit-font-smoothing: antialiased;
    }
    #bb-dashboard-welcome .fl-builder-content ul,
    #bb-dashboard-welcome .fl-builder-content ol {
        list-style: inherit;
    }
    #bb-dashboard-welcome .fl-builder-content p {
        color: inherit;
        font-size: inherit;
        margin: inherit;
        margin-bottom: 10px;
    }
    #bb-dashboard-welcome input:focus,
    #bb-dashboard-welcome textarea:focus,
    #bb-dashboard-welcome select:focus,
    #bb-dashboard-welcome button:focus {
        -webkit-box-shadow: none;
        box-shadow: none;
    }
</style>

<div id="bb-dashboard-welcome">
    <?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
        <a class="welcome-panel-close" href="<?php echo admin_url('welcome=0'); ?>"><?php _e('Dismiss'); ?></a>
    <?php } ?>
    <?php echo do_shortcode('[fl_builder_insert_layout slug="'.self::$template[self::$current_role].'"]'); ?>
</div>

<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
<script type="text/javascript" id="bb-dashboard-welcome-js">
    ;(function($) {
        $(document).ready(function() {
            $('<div id="welcome-panel" class="welcome-panel"></div>').insertBefore('#dashboard-widgets-wrap').append($('#bb-dashboard-welcome'));
        });
    })(jQuery);
</script>
<?php } ?>
