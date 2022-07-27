<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/public
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class Aixplain_Blog_Speech_Synthesis_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->processor   = new Aixplain_Blog_Speech_Synthesis_Processor();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Aixplain_Blog_Speech_Synthesis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Aixplain_Blog_Speech_Synthesis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/aixplain-blog-speech-synthesis-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Aixplain_Blog_Speech_Synthesis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Aixplain_Blog_Speech_Synthesis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/aixplain-blog-speech-synthesis-public.js', array( 'jquery' ), $this->version, false );

		add_shortcode( 'aixplain-blog-speech-synthesis', [ $this, 'register_shortcode' ] );
	}


	public function add_audio_to_post_content( $content ) {
		if ( get_option( 'aixplain_bss_placement', 'none' ) !== 'none' && get_post_type() === 'post' && is_single() ) {

			$audioURL      = $this->processor->getPostAudioLink( get_the_ID() );
			$renderedAudio = '';
			if ( ! $audioURL && current_user_can( 'administrator' ) ) {
				$renderedAudio = '<div>Post has no audio</div>';
			} else if ( $audioURL ) {
				$renderedAudio = str_replace( '$AUDIO_URL$', $audioURL, get_option( 'aixplain_bss_render' ) );
			}
			if ( get_option( 'aixplain_bss_placement', 'none' ) === 'top' ) {
				$content = $renderedAudio . $content;
			} else {
				$content = $content . $renderedAudio;
			}

		}

		return $content;
	}

	public function register_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'element' => 'audio-url',
		), $atts, 'aixplain-blog-speech-synthesis' );

		//Get API Data by short code type attribute
		//Render home page and listing page items
		if ( $atts['element'] === 'audio-url' ) {
			$audioURL      = $this->processor->getPostAudioLink( get_the_ID() );
			$renderedAudio = '';
			if ( ! $audioURL && current_user_can( 'administrator' ) ) {
				$renderedAudio = '<div>Post has no audio</div>';
			} else {
				$renderedAudio = $audioURL;
			}


			return $renderedAudio;
		} else if ( $atts['element'] === 'audio-element' ) {
			$audioURL      = $this->processor->getPostAudioLink( get_the_ID() );
			$renderedAudio = '';
			if ( ! $audioURL && current_user_can( 'administrator' ) ) {
				$renderedAudio = '<div>Post has no audio</div>';
			} else if ( $audioURL ) {
				$renderedAudio = str_replace( '$AUDIO_URL$', $audioURL, get_option( 'aixplain_bss_render' ) );
			}

			return $renderedAudio;
		}

		return '';

	}


}
