<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://gerardreches.com
 * @since             1.0.0
 * @package           Platonic
 *
 * @wordpress-plugin
 * Plugin Name:       Platonic Admin Notices
 * Plugin URI:        https://gerardreches.com
 * Description:       Modules to improve your WordPress website.
 * Version:           1.1.2
 * Author:            Gerard Reches Urbano
 * Author URI:        https://gerardreches.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       platonic
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Uses SemVer - https://semver.org
 */
define( 'PLATONIC_ADMIN_NOTICES_VERSION', '1.0.0' );

define( 'PLATONIC_ADMIN_NOTICES_PLUGIN_FILE', __FILE__ );
define( 'PLATONIC_ADMIN_NOTICES_PLUGIN_DIR', __DIR__ );
define( 'PLATONIC_ADMIN_NOTICES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

Platonic\Admin\Admin_Notices::initialize();
