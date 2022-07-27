<?php

/**
 * Fired during plugin activation
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class Aixplain_Blog_Speech_Synthesis_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_option('aixplain_bss_api_key','');
		add_option('aixplain_bss_api_url', 'https://models.aixplain.com/api/v1/execute');
		add_option('aixplain_bss_placement', 'none');
		add_option('aixplain_bss_render', '
<div class="aixplain-bss-audio">
	<audio controls>
	  <source src="$AUDIO_URL$">
	  Your browser does not support the audio tag.
	</audio>
</div>');

	}

}
