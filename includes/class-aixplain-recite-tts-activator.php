<?php

/**
 * Fired during plugin activation
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class AiXplain_Recite_TTS_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_option( AiXplain_Recite_TTS_Core::STOP_FLAG, false ,null,false);
		add_option('aixplain_bss_api_key','',null,false);
		add_option('aixplain_bss_api_url', 'https://models.aixplain.com/api/v1/execute',null,false);
		add_option('aixplain_bss_placement', 'none',null,false);
		add_option('aixplain_bss_render', '
<div class="aixplain-bss-audio">
	<audio controls>
	  <source src="$AUDIO_URL$">
	  Your browser does not support the audio tag.
	</audio>
</div>',null,false);

	}

}
