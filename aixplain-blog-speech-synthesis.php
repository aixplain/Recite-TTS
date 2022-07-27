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
 * @since             1.0.0
 * @package           Aixplain_Blog_Speech_Synthesis
 *
 * @wordpress-plugin
 * Plugin Name:       aiXplain Post Speech Synthesis
 * Plugin URI:        https://aixplain.com/
 * Description:       A plugin that synthesis posts using aiXplain platform
 * Version:           1.0.0
 * Author:            aiXplain
 * Author URI:        https://aixplain.com/
 * Text Domain:       aixplain-blog-speech-synthesis
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
define( 'AIXPLAIN_BLOG_SPEECH_SYNTHESIS_VERSION', '1.0.0' );

require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-aixplain-blog-speech-synthesis-activator.php
 */
function activate_aixplain_blog_speech_synthesis() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aixplain-blog-speech-synthesis-activator.php';
	Aixplain_Blog_Speech_Synthesis_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-aixplain-blog-speech-synthesis-deactivator.php
 */
function deactivate_aixplain_blog_speech_synthesis() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aixplain-blog-speech-synthesis-deactivator.php';
	Aixplain_Blog_Speech_Synthesis_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_aixplain_blog_speech_synthesis' );
register_deactivation_hook( __FILE__, 'deactivate_aixplain_blog_speech_synthesis' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-aixplain-blog-speech-synthesis.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_aixplain_blog_speech_synthesis() {

	$plugin = new Aixplain_Blog_Speech_Synthesis();
	$plugin->run();

}
run_aixplain_blog_speech_synthesis();
