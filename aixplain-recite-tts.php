<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://aixplain.com/
 * @since             1.0.2
 * @package           AiXplain_Recite_TTS
 *
 * @wordpress-plugin
 * Plugin Name:       Recite TTS
 * Plugin URI:        https://aixplain.com/
 * Description:       Add spoken word audio to any blog with a single API. This open source WordPress plugin leverages aiXplain’s pipeline designer to digitize blog posts into any user customizable language, and voice selection. Membership is free, and includes credit signup bonus to explore all platform options.
 * Version:           1.0.2
 * Author:            aiXplain
 * Author URI:        https://aixplain.com/
 * Text Domain:       aixplain-recite-tts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AIXPLAIN_RECITE_TTS_VERSION', '1.0.2' );

require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-aixplain-recite-tts-activator.php
 */
function activate_aixplain_recite_tts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aixplain-recite-tts-activator.php';
	AiXplain_Recite_TTS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-aixplain-recite-tts-deactivator.php
 */
function deactivate_axiplain_recite_tts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aixplain-recite-tts-deactivator.php';
	AiXplain_Recite_TTS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_aixplain_recite_tts' );
register_deactivation_hook( __FILE__, 'deactivate_axiplain_recite_tts' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-aixplain-recite-tts.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_aixplain_recite_tts() {

	$plugin = new AiXplain_Recite_TTS();
	$plugin->run();

}
run_aixplain_recite_tts();
