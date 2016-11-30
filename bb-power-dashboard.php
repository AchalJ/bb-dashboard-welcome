<?php

/**
 * Plugin Name: Power Dashboard for Beaver Builder
 * Description: Replaces the default WordPress dashboard with a Beaver Builder template.
 * Author: Beaver Addons
 * Author URI: https://wpbeaveraddons.com
 * Version: 1.0.0
 * Copyright: (c) 2016 IdeaBox Creations
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'BBPD_VER', '1.0.0' );
define( 'BBPD_DIR', plugin_dir_path( __FILE__ ) );
define( 'BBPD_URL', plugins_url( '/', __FILE__ ) );
define( 'BBPD_PATH', plugin_basename( __FILE__ ) );

require_once 'classes/class-pd-admin.php';
