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
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class AiXplain_Recite_TTS_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'aixplain-recite-tts',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
