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
     * Holds the Beaver Builder user templates site ID.
     *
     * @since 1.0.2
     * @access protected
     * @var array
     */
    static protected $template_site;

    /**
     * Holds the option.
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    static protected $dismissible;

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
     * Holds the CSS classes.
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    static protected $classes;

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
     * Trigger hooks and actions on plugins_loaded.
     *
     * @since 1.0.0
     * @return void
     */
    static public function admin_init()
    {
        self::save_settings();
        self::delete_option();

        global $wp_roles;

        self::$roles            = $wp_roles->get_names();
        self::$current_role     = self::get_current_role();
        self::$template         = self::get_option( 'bbpd_template' );
        self::$dismissible      = self::get_option( 'bbpd_template_dismissible' );
        self::$template_site    = self::get_option( 'bbpd_template_site' );

        global $pagenow;
        if ( 'index.php' != $pagenow ) {
            return;
        }

        if ( is_array( self::$template ) &&
                isset( self::$template[self::$current_role] ) &&
                    self::$template[self::$current_role] != 'none' ) {

            remove_action( 'welcome_panel', 'wp_welcome_panel' );
            add_action( 'welcome_panel', __CLASS__ . '::welcome_panel' );

            if ( ! current_user_can( 'edit_theme_options' ) ) {
                self::$classes = 'welcome-panel';
                add_action( 'admin_notices', __CLASS__ . '::welcome_panel' );
            }
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

        if ( ! class_exists( 'FLBuilder' ) ) {
            return;
        }

        add_action( 'admin_enqueue_scripts', __CLASS__ . '::load_scripts' );

        global $pagenow;
        if ( 'index.php' == $pagenow ) {
            add_action( 'admin_enqueue_scripts',  'FLBuilder::register_layout_styles_scripts' );
        }

        // Add settings to BB's options panel
        if ( is_multisite() ) {
            $hide_settings = get_blog_option( 1, 'bbpd_hide_from_subsite' );
            if ( $hide_settings == false ) {
                self::bb_nav_hooks();
            }
            if ( $hide_settings == true && get_current_blog_id() == 1  ) {
                self::bb_nav_hooks();
            }
        }
        else {
		    self::bb_nav_hooks();
        }

		// Save settings
		add_action( 'fl_builder_admin_settings_save', __CLASS__ . '::save_settings' );
    }

    /**
	 * Load scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
    static public function load_scripts()
    {
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'fl-builder-settings' ) {
            wp_enqueue_style( 'bbpd-style', DWBB_URL . 'assets/css/admin.css', array(), rand() );
        }
    }

    /**
     * Trigger hooks on function call.
     *
     * @since 1.0.3
     * @return void
     */
    static public function bb_nav_hooks()
    {
        add_filter( 'fl_builder_admin_settings_nav_items', __CLASS__ . '::bb_nav_items' );
        add_action( 'fl_builder_admin_settings_render_forms', __CLASS__ . '::bb_nav_forms' );
    }

    /**
     * Hook the setting label and custom title in BB settings.
     *
     * @since 1.0.0
     * @return mixed
     */
    static public function bb_nav_items( $items )
    {
        $items['bb-dashboard-welcome'] = array(
			'title' 	=> __( 'Dashboard Welcome', 'bbpd' ),
			'show'		=> true,
			'priority'	=> 750
		);

		return $items;
    }

    /**
     * Render plugin settings page.
     *
     * @since 1.0.0
     * @return void
     */
    static public function bb_nav_forms()
    {
        self::$templates = self::get_bb_templates();
        require_once DWBB_DIR . 'includes/admin-settings.php';
    }

    /**
     * Output the content for power dashboard.
     *
     * @since 1.0.0
     * @return void
     */
    static public function welcome_panel()
    {
        include DWBB_DIR . 'includes/welcome-panel.php';
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

        self::update_option( 'bbpd_template', $_POST['bbpd_template'] );
        self::update_option( 'bbpd_template_dismissible', $_POST['bbpd_template_dismissible'] );

        if ( isset( $_POST['bbpd_template_site'] ) ) {
            self::update_option( 'bbpd_template_site', $_POST['bbpd_template_site'] );
        }

        if ( isset( $_POST['bbpd_hide_from_subsite'] ) ) {
            self::update_option( 'bbpd_hide_from_subsite', true );
        }
        else {
            delete_option( 'bbpd_hide_from_subsite' );
        }
    }

    /**
	 * Renders template via shortcode.
	 *
	 * @since 1.0.2
	 * @access public
	 * @return mixed
	 */
    static public function render_template()
    {
        $site = '';

        if ( is_array( self::$template_site ) && isset( self::$template_site[self::$current_role] ) ) {
            $site = self::$template_site[self::$current_role];
        }

        if ( empty( $site ) ) {
            echo do_shortcode('[fl_builder_insert_layout slug="' . self::$template[self::$current_role] . '"]');
        } else {
            echo do_shortcode('[fl_builder_insert_layout slug="' . self::$template[self::$current_role] . '" site="1"]');
        }
    }

    /**
	 * Returns an option from the database for
	 * the admin settings page.
	 *
	 * @since 1.0.3
	 * @param string $option The option key.
     * @param bool $network_override Multisite setting override check.
	 * @return mixed
	 */
    static private function get_option( $option, $network_override = true )
    {
        if ( is_network_admin() ) {
            $value = get_site_option( $option );
        }
        elseif ( ! $network_override && is_multisite() ) {
            $value = get_site_option( $option );
            $value = false === $value ? get_blog_option( 1, $option ) : $value;
        }
        elseif ( $network_override && is_multisite() ) {
            $value = get_option( $option );
            $value = false === $value ? get_blog_option( 1, $option ) : $value;
        }
        else {
            $value = get_option( $option );
        }

        return $value;
    }

    /**
	 * Updates an option from the admin settings page.
	 *
	 * @since 1.0.3
	 * @param string $option The option key.
	 * @param mixed $value The value to update.
	 * @return mixed
	 */
    static private function update_option( $option, $value )
    {
        if ( is_network_admin() ) {
            update_site_option( $option, $value );
        } else {
            update_option( $option, $value );
        }
    }

    /**
	 * Deletes an option from the admin settings page
     * via URL parameter.
	 *
	 * @since 1.0.3
	 * @return mixed
	 */
    static private function delete_option()
    {
        if ( isset( $_GET['delete_option'] ) && !empty( $_GET['delete_option'] ) ) {
            delete_option( 'bbpd_template' );
            delete_option( 'bbpd_template_dismissible' );
            delete_option( 'bbpd_template_site' );
        }
    }

    /**
     * Get current user role.
     *
     * @since 1.0.0
     * @return string
     */
    static private function get_current_role()
    {
        // Get current user role in multisite network using WP_User_Query.
        if ( is_multisite() ) {
            $user_query = new WP_User_Query( array( 'blog_id' => 1 , 'include' => array( get_current_user_id() ) ) );
            if ( ! empty( $user_query->results ) ) {
            	$roles = $user_query->results[0]->roles;
                if ( is_array( $roles ) && count( $roles ) ) {
                    return $roles[0];
                }
            }
        }

        $user   = wp_get_current_user();
        $roles  = $user->roles;
        $roles  = array_shift( $roles );

        return $roles;
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

        $args = array(
			'post_type'       => 'fl-builder-template',
			'orderby'         => 'title',
			'order'           => 'ASC',
			'posts_per_page'  => '-1',
			'tax_query'       => array(
				array(
					'taxonomy'  => 'fl-builder-template-type',
					'field'     => 'slug',
					'terms'     => array( 'layout', 'row' )
				)
			)
		);

        $posts = get_posts( $args );

        // Multisite support.
        // @since 1.0.2
        if ( is_multisite() ) {

            $blog_id = get_current_blog_id();

            if ( $blog_id != 1 ) {
                switch_to_blog(1);

                // Get posts from main site.
                $main_posts = get_posts( $args );

                // Loop through each main site post
                // and add site_id to post object.
                foreach ( $main_posts as $main_post ) {
                    $main_post->site_id = 1;
                }

                $posts = array_merge( $posts, $main_posts );

                restore_current_blog();
            }
            else {
                // Loop through each main site post
                // and add site_id to post object.
                foreach ( $posts as $post ) {
                    $post->site_id = 1;
                }
            }
        }

		foreach( $posts as $post ) {
			$templates[] = array(
				'slug' => $post->post_name,
				'name' => $post->post_title,
                'site' => isset( $post->site_id ) ? $post->site_id : null
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
    static private function get_selected( $key = '', $value = '', $data = array() )
    {
        $selected = ' selected="selected"';
        if ( is_array( $data ) && isset( $data[$key] ) && $data[$key] == $value ) {
            return $selected;
        }
        if ( ! is_array( $data ) || count( $data ) == 0 ) {
            if ( $key == $value ) {
                return $selected;
            }
        }
    }
}

BB_Power_Dashboard_Admin::init();
