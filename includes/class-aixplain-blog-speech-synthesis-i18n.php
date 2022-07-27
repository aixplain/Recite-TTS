<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class Aixplain_Blog_Speech_Synthesis_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'aixplain-blog-speech-synthesis',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
