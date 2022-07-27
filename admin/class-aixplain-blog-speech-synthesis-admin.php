<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/admin
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class Aixplain_Blog_Speech_Synthesis_Admin {

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
		$this->processor = new Aixplain_Blog_Speech_Synthesis_Processor();

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
		 * defined in Aixplain_Blog_Speech_Synthesis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Aixplain_Blog_Speech_Synthesis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/aixplain-blog-speech-synthesis-admin.css', array(), $this->version, 'all' );

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
		 * defined in Aixplain_Blog_Speech_Synthesis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Aixplain_Blog_Speech_Synthesis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/aixplain-blog-speech-synthesis-admin.js', array( 'jquery' ), $this->version.'.03', false );

	}

	/**
	 * Register Menu for the admin area
	 */
	public function register_menu() {
		$view = function ( $args ) {
			global $totalPosts, $totalSynthesisedPosts;
			$totalPosts = wp_count_posts();
			$totalPosts            = $totalPosts->publish;// + $totalPosts->draft
			$totalSynthesisedPosts = $this->processor->getSynthesisedPostsCount();

			return include plugin_dir_path( __FILE__ ) . 'partials/aixplain-blog-speech-synthesis-admin-display.php';
		};
		add_menu_page( 'aiXplain BSS', 'aiXplain BSS', 'manage_options', 'aixplain-bss', $view );//'https://aixplain.com/wp-content/uploads/2021/05/Round-150x150.png'
	}


	public function register_synthesis_action() {
		$this->processor->synthesizeNextPost();

		$totalPosts = wp_count_posts();
		$totalPosts            = $totalPosts->publish;// + $totalPosts->draft
		$totalSynthesisedPosts = $this->processor->getSynthesisedPostsCount();
		wp_send_json([
			'total'     => $totalPosts,
			'processed' => $totalSynthesisedPosts,
		] );
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function register_synthesize_post_action() {
		$this->processor->synthesizePost($_POST['post_id']);

		wp_send_json([
			'url'     => $this->processor->getPostAudioLink($_POST['post_id']),
		] );
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function register_unsynthesize_post_action() {
		$this->processor->unSynthesizePost($_POST['post_id']);

		wp_send_json([
			"success"     => true,
		] );
		wp_die(); // this is required to terminate immediately and return a proper response
	}




	public function on_attachment_delete($attachment){
		$this->processor->markPostAsUnSynthesisedFromAttachment($attachment);
	}

	public function on_post_published($post_id){
		$audioLink = $this->processor->getPostAudioLink( $post_id );
		if(!$audioLink) {
			$this->processor->synthesizePost( $post_id );
		}
	}

	public function add_column_to_posts( $columns ) {
		return array_merge( $columns, [ 'synthesized' => __( 'Synthesized', $this->plugin_name ) ] );
	}

	public function add_content_to_posts_column( $column_key  ) {
		if ( $column_key == 'synthesized' ) {
			$audioUrl = $this->processor->getPostAudioLink( get_the_ID() );
			if ( $audioUrl ) {
				echo '<a href="'.$audioUrl.'" target="_blank" >';
				echo __( 'Yes', $this->plugin_name );
				echo '</a>, ';
				echo '<a href="javascript:void(0)" style="color:orange;" class="un-synthesize-action" data-id="'.get_the_ID().'" >';
				echo __( 'Reset', $this->plugin_name );
				echo '</a>';
			} else {
				echo 'No, <a href="javascript:void(0)" style="color:red;" class="synthesize-action" data-id="'.get_the_ID().'">';
				echo __( 'synthesize', $this->plugin_name );
				echo '</a>';
			}
		}
	}

}
