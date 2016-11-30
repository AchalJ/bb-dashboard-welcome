<?php
/**
 * Handles logic for the WordPress dashboard and admin settings.
 *
 * @since 1.0.0
 */

final class BB_Power_Dashboard_Admin {

    /**
     * Holds the admin menu label.
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    static protected $title;

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
        add_action( 'plugins_loaded', __CLASS__ . '::init_hooks' );
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

        self::save_settings();

        if ( current_user_can( 'read' ) ) {
            add_action( 'admin_enqueue_scripts', __CLASS__ . '::load_scripts' );
            add_filter( 'admin_title', __CLASS__ . '::admin_title', 10, 2 );
            add_action( 'admin_menu', __CLASS__ . '::admin_menu' );
            add_action( 'current_screen', __CLASS__ . '::current_screen' );
        }

        global $wp_roles;
        self::$roles        = $wp_roles->get_names();
        self::$current_role = array_shift( wp_get_current_user()->roles );
        self::$title        = __('Home', 'bbpd');
    }

    static public function load_scripts()
    {
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'pd-settings' ) {
            wp_enqueue_style( 'bbpd-style', BBPD_URL . 'assets/css/admin.css', array(), rand() );
        }
    }

    /**
     * Page title.
     *
     * @since 1.0.0
     * @param string $admin_title
     * @param string $title
     * @return string $admin_title
     */
    static public function admin_title( $admin_title, $title )
    {
        global $pagenow;
        if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'power-dashboard' == $_GET['page'] ) {
            $admin_title = self::$title . $admin_title;
        }
        return $admin_title;
    }

    /**
     * Output the content for power dashboard.
     *
     * @since 1.0.0
     * @return void
     */
    static public function dashboard_content()
    {
        $template = get_option( 'bbpd_template' );
        $slug = $template[self::$current_role];
        echo '<style>.fl-builder-content{margin-left:-20px;}</style>';
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
        /**
         * Adds a custom page to admin
         */
        add_menu_page( self::$title, '', 'manage_options', 'power-dashboard', __CLASS__ . '::dashboard_content' );

        /**
         * Remove the custom page from the admin menu
         */
        remove_menu_page('power-dashboard');

        /**
         * Make dashboard menu item the active item
         */
        global $parent_file, $submenu_file;
        $parent_file = 'index.php';
        $submenu_file = 'index.php';

        /**
         * Rename the dashboard menu item
         */
        //global $menu;
        //$menu[2][0] = self::$title;

        /**
         * Rename the dashboard submenu item
         */
        global $submenu;
        $submenu['index.php'][0][0] = self::$title;

        /**
         * Power Dashboard Settings menu
         */
         if ( current_user_can( 'manage_options' ) ) {

 			$title = __('Power Dashboard', 'bbpd');
 			$cap   = 'manage_options';
 			$slug  = 'pd-settings';
 			$func  = __CLASS__ . '::settings_page';

 			add_submenu_page( 'options-general.php', $title, $title, $cap, $slug, $func );
 		}
    }


    /**
     * Redirect users from the normal dashboard to power dashboard.
     *
     * @since 1.0.0
     * @param object $screen
     * @return void
     */
    static public function current_screen( $screen )
    {
        $template = get_option( 'bbpd_template' );
        if( 'dashboard' == $screen->id && is_array( $template ) && isset( $template[self::$current_role] ) && $template[self::$current_role] != 'none' ) {
            wp_safe_redirect( admin_url('admin.php?page=power-dashboard') );
            exit;
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
        include BBPD_DIR . 'includes/admin-settings.php';
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
