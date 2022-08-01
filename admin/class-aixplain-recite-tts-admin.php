<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/admin
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class AiXplain_Recite_TTS_Admin {

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


	private $processor;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->processor   = new AiXplain_Recite_TTS_Core();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AiXplain_Blog_Speech_Synthesis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AiXplain_Blog_Speech_Synthesis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/aixplain-recite-tts-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AiXplain_Blog_Speech_Synthesis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AiXplain_Blog_Speech_Synthesis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/aixplain-recite-tts-admin.js', array( 'jquery' ), $this->version . '.03', false );

	}
	/***********************************************************************************************
	 *************************** Pages ********************************************************
	 **********************************************************************************************/
	/**
	 * Register Menu for the admin area
	 */
	public function register_menu() {
		$view = function ( $args ) {
			global $totalPosts, $totalSynthesisedPosts, $toBeProcessed, $notPossibleToProcess, $jobAlreadyRunning, $postsHasApiError, $stopFlag;
			$totalPosts            = wp_count_posts();
			$totalPosts            = $totalPosts->publish;// + $totalPosts->draft
			$totalSynthesisedPosts = $this->processor->getSynthesisedPostsCount();
			$notPossibleToProcess  = $this->processor->getNoneSynthesizablePostsCount();
			$postsHasApiError      = $this->processor->getPostsHasApiErrorCount();
			$jobAlreadyRunning     = $this->processor->isSynthesizeAllPostsJobRunning();
			$toBeProcessed         = $totalPosts - $totalSynthesisedPosts - $notPossibleToProcess - $postsHasApiError;
			$stopFlag              = get_option( AiXplain_Recite_TTS_Core::STOP_FLAG, false );

			return include plugin_dir_path( __FILE__ ) . 'partials/aixplain-recite-tts-admin-display.php';
		};
		add_menu_page( $this->plugin_name, $this->plugin_name, 'manage_options', 'aixplain-bss', $view, site_url("wp-content/plugins/aixplain-recite-tts/admin/images/icon.png") );

	}
	/***********************************************************************************************
	 *************************** Ajax Actions ********************************************************
	 **********************************************************************************************/
	/**
	 *
	 */
	public function register_synthesize_all_posts_action() {

		$stats = $this->_get_posts_stats();
		if ( ! $stats["jobAlreadyRunning"] && $stats["tobeSynthesized"] > 0 ) {
			$stats["jobAlreadyRunning"] = $this->processor->startSynthesizeAllPostsJob();
		}
		$this->_get_response( $stats );
	}

	/**
	 *
	 */
	public function register_reset_api_errors_posts_action() {
		$this->processor->markPostsHaveApiErrorAsUnSynthesised();
		$stats = $this->_get_posts_stats();
		$this->_get_response( $stats );
	}

	/**
	 *
	 */
	public function register_stop_synthesize_all_posts_action() {

		$stats = $this->_get_posts_stats();
		if ( $stats["jobAlreadyRunning"] ) {
			$stats["jobAlreadyRunning"] = $this->processor->stopSynthesizeAllPostsJob();
			//refresh stats
			$stats = $this->_get_posts_stats();
		}
		$this->_get_response( $stats );
	}


	/**
	 *
	 */
	public function register_synthesize_all_posts_in_background_action() {
		AiXplain_Recite_TTS_Core::log( 'Start ' . date( 'Y-m-d hh:ii:ss' ) );
		do {
			//Check stop flag
			//Force delete from cache
			$GLOBALS['wp_object_cache']->delete( AiXplain_Recite_TTS_Core::STOP_FLAG, 'options' );
			//Get from db
			$stopFlag = get_option( AiXplain_Recite_TTS_Core::STOP_FLAG, false );
			if ( $stopFlag ) {
				break;
			}
			try {
				$this->processor->synthesizeNextPost();
			} catch ( Exception $e ) {
				AiXplain_Recite_TTS_Core::log( $e->getMessage() );
			}
			$stats = $this->_get_posts_stats();
			AiXplain_Recite_TTS_Core::log( 'Finished 1 Post - ' . $stats["totalPosts"] . ' - ' . $stats["totalSynthesisedPosts"] );

		} while ( ( $stats["totalPosts"] - $stats["notPossibleToProcess"] - $stats["postsHasApiError"] ) > $stats["totalSynthesisedPosts"] );
		//Reset stop flag
		update_option( AiXplain_Recite_TTS_Core::STOP_FLAG, false );
		AiXplain_Recite_TTS_Core::log( 'Finished All Posts ' . date( 'Y-m-d hh:ii:ss' ) );

	}

	/**
	 * @throws Exception
	 */
	public function register_synthesize_post_action() {
		$error = $this->processor->synthesizePost( $_POST['post_id'] );

		wp_send_json( [
			'url'   => $this->processor->getPostAudioLink( $_POST['post_id'] ),
			"error" => strip_tags( $error )
		] );
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 *
	 */
	public function register_unsynthesize_post_action() {
		$this->processor->unSynthesizePost( $_POST['post_id'] );

		wp_send_json( [
			"success" => true,
		] );
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	/***********************************************************************************************
	 *************************** On Events ************* ******************************************
	 **********************************************************************************************/
	/**
	 * @param $attachment
	 */
	public function on_attachment_delete( $attachment ) {
		$this->processor->markPostAsUnSynthesisedFromAttachment( $attachment );
	}

	/**
	 * TODO: implement async approach on `on_post_published`
	 *
	 * @param $post_id
	 *
	 * @throws Exception
	 */
	public function on_post_published( $post_id ) {
		$audioLink = $this->processor->getPostAudioLink( $post_id );
		if ( ! $audioLink ) {
			$this->processor->synthesizePost( $post_id );
		}
	}
	/***********************************************************************************************
	 *************************** All Posts Table Actions **********************************************
	 **********************************************************************************************/
	/**
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_column_to_posts( $columns ) {
		return array_merge( $columns, [ 'synthesized' => __( 'Synthesized', $this->plugin_name ) ] );
	}

	/**
	 * @param $column_key
	 */
	public function add_content_to_posts_column( $column_key ) {
		if ( $column_key == 'synthesized' ) {
			$audioUrl = $this->processor->getPostAudioLink( get_the_ID() );
			if ( $audioUrl ) {
				echo '<a href="' . $audioUrl . '" target="_blank" >';
				echo __( 'Yes', $this->plugin_name );
				echo '</a>, ';
				echo '<a href="javascript:void(0)" style="color:orange;" class="un-synthesize-action" data-id="' . get_the_ID() . '" >';
				echo __( 'Reset', $this->plugin_name );
				echo '</a>';

			} else {
				$errorMessage = $this->processor->getPostErrorMessage( get_the_ID() );
				if ( $errorMessage ) {
					echo '<span title="Error Message: ' . $errorMessage . '" style="text-decoration: underline">Failed</span> <a title=" Error Message: ' . $errorMessage . '" href="javascript:void(0)" style="color:red;" class="synthesize-action" data-id="' . get_the_ID() . '">';
					echo __( 'Try again?', $this->plugin_name );
					echo '</a>';
				} else {
					echo '<span >No</span>, <a href="javascript:void(0)" style="color:red;" class="synthesize-action" data-id="' . get_the_ID() . '">';
					echo __( 'Synthesize', $this->plugin_name );
					echo '</a>';
				}
			}
		}
	}
	/***********************************************************************************************
	 *************************** Internal Functions **********************************************
	 **********************************************************************************************/
	/**
	 * @return array
	 */
	private function _get_posts_stats() {

		$totalPosts            = wp_count_posts();
		$totalPosts            = $totalPosts->publish;// + $totalPosts->draft
		$totalSynthesisedPosts = $this->processor->getSynthesisedPostsCount();
		$notPossibleToProcess  = $this->processor->getNoneSynthesizablePostsCount();
		$postsHasApiError      = $this->processor->getPostsHasApiErrorCount();
		$tobeSynthesized       = $totalPosts - $totalSynthesisedPosts - $notPossibleToProcess - $postsHasApiError;
		$jobAlreadyRunning     = $this->processor->isSynthesizeAllPostsJobRunning();

		return [
			"totalPosts"            => $totalPosts,
			"totalSynthesisedPosts" => $totalSynthesisedPosts,
			"notPossibleToProcess"  => $notPossibleToProcess,
			"postsHasApiError"      => $postsHasApiError,
			"tobeSynthesized"       => $tobeSynthesized,
			"jobAlreadyRunning"     => $jobAlreadyRunning,
		];
	}

	/**
	 * @param $stats
	 */
	private function _get_response( $stats ) {

		wp_send_json( [
			'total'                   => $stats["totalPosts"],
			'processed'               => $stats["totalSynthesisedPosts"],
			'to_be_processed'         => $stats["tobeSynthesized"],
			'posts_has_api_error'     => $stats["postsHasApiError"],
			'not_possible_to_process' => $stats["notPossibleToProcess"],
			'isRunning'               => $stats["jobAlreadyRunning"],
		] );
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	function process_job_increase_time_limit( $time_limit ) {
		return 60 * 60 * 48;
	}

}
