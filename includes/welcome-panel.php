<style>
    .welcome-panel {
        padding: 0;
    }
    .welcome-panel .welcome-panel-close {
        z-index: 1;
    }
</style>

<?php
    echo do_shortcode('[fl_builder_insert_layout slug="'.self::$template[self::$current_role].'"]');
?>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        if ( $('.fl-builder-content').find('.fa').length > 0 ) {
            $('.fl-builder-content').prepend('<link rel="stylesheet" id="font-awesome-css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css" media="all">');
        }
    });
</script>
