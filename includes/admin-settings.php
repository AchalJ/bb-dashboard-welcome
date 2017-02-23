<div id="fl-bb-dashboard-welcome-form" class="fl-settings-form">
    <h2 style="display: none;"><?php _e('Dashboard Welcome', 'bbpd'); ?></h2>
    <form method="post" id="pd-settings-form" action="<?php FLBuilderAdminSettings::render_form_action( 'bb-dashboard-welcome' ); ?>">
        <div class="icon32 icon32-pd-settings" id="icon-pd"><br /></div>
        <table class="bbpd-settings-table wp-list-table widefat">
            <tr valign="top">
                <th scope="row" valign="top">
                    <strong><?php esc_html_e('User Role', 'bbpd'); ?></strong>
                </th>
                <th scope="row" valign="top">
                    <strong><?php esc_html_e('Select Template', 'bbpd'); ?></strong>
                </th>
                <th scope="row" valign="top">
                    <strong><?php esc_html_e('Is Dismissible?', 'bbpd'); ?></strong>
                </th>
            </tr>
            <?php $count = 0; foreach ( self::$roles as $key => $value ) : ?>
                <tr class="<?php echo $count % 2 == 0 ? 'alternate' : ''; ?>">
                    <td><?php echo $value; ?></td>
                    <td>
                        <select name="bbpd_template[<?php echo $key; ?>]" class="bbpd-input bbpd-template-select">
                            <option value="none"<?php echo self::get_selected( $key, 'none', self::$template ); ?>><?php esc_html_e('None', 'bbpd'); ?></option>
                            <?php foreach ( self::$templates as $template ) { ?>
                                <option data-site="<?php echo $template['site']; ?>" value="<?php echo $template['slug']; ?>"<?php echo self::get_selected( $key, $template['slug'], self::$template ); ?>><?php echo $template['name']; ?></option>
                            <?php } ?>
                        </select>
                        <?php if ( is_multisite() ) { ?>
                            <input type="hidden" name="bbpd_template_site[<?php echo $key; ?>]" value="<?php echo isset(self::$template_site[$key]) ? self::$template_site[$key] : ''; ?>" />
                        <?php } ?>
                    </td>
                    <td>
                        <select name="bbpd_template_dismissible[<?php echo $key; ?>]" class="bbpd-input">
                            <option value="yes"<?php echo self::get_selected( $key, 'yes', self::$dismissible ); ?>><?php esc_html_e('Yes', 'bbpd'); ?></option>
                            <option value="no"<?php echo self::get_selected( $key, 'no', self::$dismissible ); ?>><?php esc_html_e('No', 'bbpd'); ?></option>
                        </select>
                    </td>
                </tr>
            <?php $count++; endforeach; ?>
        </table>

        <?php if ( is_multisite() && get_current_blog_id() == 1 ) { ?>
            <p>
                <label>
                    <input type="checkbox" value="1" name="bbpd_hide_from_subsite" <?php if ( get_option( 'bbpd_hide_from_subsite' ) == true ) { echo 'checked="checked"'; } ?> />
                    <?php _e( 'Hide settings from network subsites', 'bbpd' ); ?>
                </label>
            </p>
        <?php } ?>

        <?php submit_button(); ?>
        <?php wp_nonce_field('bbpd-settings', 'bbpd-settings-nonce'); ?>
    </form>

    <?php if ( is_multisite() ) { ?>
    <script type="text/javascript">
    (function ($) {
        <?php $data = array(); foreach ( self::$templates as $template ) {
            $data[$template['slug']] = $template['site'];
        } ?>
        var data = <?php echo json_encode( $data ); ?>;
        $('.bbpd-template-select').on('change', function() {
            var value = $(this).val();
            var siteId = data[value];
            $(this).parent().find('input[type="hidden"]').val(siteId);
        });
    })(jQuery);
    </script>
    <?php } ?>

</div>
