<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://apkee.hk/kedi
 * @since             1.0.0
 * @package           Woo_Octopus
 *
 * @wordpress-plugin
 * Plugin Name:       Woo Octopus
 * Plugin URI:        https://apkee.hk
 * Description:       Octopus 八達通 收錢 payment gateway. Built on 2020-Feb
 * Version:           1.1.0
 * Author:            paulus
 * Author URI:        https://apkee.hk/kedi
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-octopus
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;		//* 作用同下一行，不過係喺 WordPress 啟動時最先 define 嘅，相重保護。
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_OCTOPUS_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-octopus-activator.php
 */
function activate_woo_octopus() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-octopus-activator.php';
	Woo_Octopus_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-octopus-deactivator.php
 */
function deactivate_woo_octopus() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-octopus-deactivator.php';
	Woo_Octopus_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_octopus' );
register_deactivation_hook( __FILE__, 'deactivate_woo_octopus' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-octopus.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_octopus() {

	$plugin = new Woo_Octopus();
	$plugin->run();

}
run_woo_octopus();
