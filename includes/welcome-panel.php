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
