<?php
/**
 * Handles logic for the WordPress dashboard and admin settings.
 *
 * @since 1.0.0
 */

final class BB_Power_Dashboard_Admin {

    /**
     * Holds the settings value.
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    static protected $template;

    /**
     * Holds the Beaver Builder user templates data.
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    static protected $templates;

    /**
     * Holds the user roles.
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    static protected $roles;

    /**
     * Holds the current user role.
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    static protected $current_role;

    /**
	 * Initializes the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
    static public function init()
    {
        add_action( 'admin_init', __CLASS__ . '::admin_init' );
        add_action( 'plugins_loaded', __CLASS__ . '::init_hooks' );
    }

    /**
     * Trigger hooks and actions on admin_init.
     *
     * @since 1.0.0
     * @return void
     */
    static public function admin_init()
    {
        self::save_settings();

        global $wp_roles;

        self::$roles        = $wp_roles->get_names();
        self::$current_role = array_shift( wp_get_current_user()->roles );
        self::$template     = get_option( 'bbpd_template' );

        if ( is_array( self::$template ) && isset( self::$template[self::$current_role] ) && self::$template[self::$current_role] != 'none' ) {
            remove_action( 'welcome_panel', 'wp_welcome_panel' );
            add_action( 'welcome_panel', __CLASS__ . '::dashboard_content' );
        }
    }

    /**
     * Trigger hooks and actions.
     *
     * @since 1.0.0
     * @return void
     */
    static public function init_hooks()
    {
        if ( ! is_admin() ) {
			return;
		}

        add_action( 'admin_enqueue_scripts', __CLASS__ . '::load_scripts' );

        if ( current_user_can( 'read' ) ) {
            add_action( 'admin_menu', __CLASS__ . '::admin_menu' );
        }
    }

    /**
	 * Load scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
    static public function load_scripts()
    {
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'pd-settings' ) {
            wp_enqueue_style( 'bbpd-style', DWBB_URL . 'assets/css/admin.css', array(), rand() );
        }
    }

    /**
     * Output the content for power dashboard.
     *
     * @since 1.0.0
     * @return void
     */
    static public function dashboard_content()
    {
        $template = self::$template;
        $slug = $template[self::$current_role];
        echo '<style>.welcome-panel {padding: 0;} .welcome-panel .welcome-panel-close {z-index: 1;}</style>';
        echo do_shortcode('[fl_builder_insert_layout slug="'.$slug.'"]');
    }

    /**
     * Hook the setting label and custom title in admin menu.
     *
     * @since 1.0.0
     * @return mixed
     */
    static public function admin_menu()
    {
        if ( current_user_can( 'manage_options' ) ) {

 			$title = __('DWBB', 'bbpd');
 			$cap   = 'manage_options';
 			$slug  = 'pd-settings';
 			$func  = __CLASS__ . '::settings_page';

 			add_submenu_page( 'options-general.php', $title, $title, $cap, $slug, $func );
 		}
    }

    /**
     * Render plugin settings page.
     *
     * @since 1.0.0
     * @return void
     */
    static public function settings_page()
    {
        self::$template  = get_option( 'bbpd_template' );
        self::$templates = self::get_bb_templates();
        include DWBB_DIR . 'includes/admin-settings.php';
    }

    /**
     * Save settings.
     *
     * @since 1.0.0
     * @return void
     */
    static public function save_settings()
    {
        if( ! isset( $_POST['bbpd-settings-nonce'] ) || ! wp_verify_nonce( $_POST['bbpd-settings-nonce'], 'bbpd-settings' ) ) {
            return;
        }
        update_option( 'bbpd_template', $_POST['bbpd_template'] );
    }

    /**
	 * Returns user template data of a certain type for the UI.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param string $type
	 * @return array
	 */
	static private function get_bb_templates( $type = 'layout' )
	{
		$templates = array();

		foreach( get_posts( array(
			'post_type'       => 'fl-builder-template',
			'orderby'         => 'title',
			'order'           => 'ASC',
			'posts_per_page'  => '-1',
			'tax_query'       => array(
				array(
					'taxonomy'  => 'fl-builder-template-type',
					'field'     => 'slug',
					'terms'     => $type
				)
			)
		) ) as $post ) {
			$templates[] = array(
				'slug' => $post->post_name,
				'name' => $post->post_title
			);
		}

		return $templates;
	}

    /**
     * Returns the selected attribute for select tag.
     *
     * @since 1.0.0
     * @param string $key
     * @param string $value
     * @param array $data
     * @return string
     */
    static public function get_selected( $key = '', $value = '', $data = array() )
    {
        $selected = ' selected="selected"';
        if ( is_array( $data ) && isset( $data[$key] ) && $data[$key] == $value ) {
            return $selected;
        }
        if ( !is_array( $data ) || count( $data ) == 0 ) {
            if ( $key == $value ) {
                return $selected;
            }
        }
    }
}

BB_Power_Dashboard_Admin::init();
