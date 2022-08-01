<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class AiXplain_Recite_TTS {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      AiXplain_Recite_TTS_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AIXPLAIN_RECITE_TTS_VERSION' ) ) {
			$this->version = AIXPLAIN_RECITE_TTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'Recite TTS';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - AiXplain_Blog_Speech_Synthesis_Loader. Orchestrates the hooks of the plugin.
	 * - AiXplain_Blog_Speech_Synthesis_i18n. Defines internationalization functionality.
	 * - AiXplain_Blog_Speech_Synthesis_Admin. Defines all hooks for the admin area.
	 * - AiXplain_Blog_Speech_Synthesis_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 *
		 * Require Libraries
		 */
		require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/libraries/action-scheduler/action-scheduler.php' );
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aixplain-recite-tts-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aixplain-recite-tts-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-aixplain-recite-tts-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-aixplain-recite-tts-public.php';

		/**
		 * The class responsible for core functionalities of plugin: processing the posts and generating audio...
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aixplain-recite-tts-core.php';

		$this->loader = new AiXplain_Recite_TTS_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the AiXplain_Blog_Speech_Synthesis_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new AiXplain_Recite_TTS_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new AiXplain_Recite_TTS_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_synthesize_all_posts', $plugin_admin, 'register_synthesize_all_posts_action' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_stop_synthesize_all_posts', $plugin_admin, 'register_stop_synthesize_all_posts_action' );
		$this->loader->add_action( 'delete_attachment', $plugin_admin, 'on_attachment_delete' );
		$this->loader->add_filter( 'manage_post_posts_columns', $plugin_admin, 'add_column_to_posts' );
		$this->loader->add_action( 'manage_post_posts_custom_column', $plugin_admin, 'add_content_to_posts_column' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_synthesize_single_post', $plugin_admin, 'register_synthesize_post_action' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_reset_api_errors_posts', $plugin_admin, 'register_reset_api_errors_posts_action' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_unsynthesize_single_post', $plugin_admin, 'register_unsynthesize_post_action' );
		$this->loader->add_action( AiXplain_Recite_TTS_Core::BACKGROUND_JOB_HOOK, $plugin_admin, 'register_synthesize_all_posts_in_background_action' );
		$this->loader->add_filter( 'action_scheduler_queue_runner_time_limit', $plugin_admin, 'process_job_increase_time_limit' );
		//TODO: implement async approach on `on_post_published`
		//$this->loader->add_action( 'publish_post', $plugin_admin, 'on_post_published' );


	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new AiXplain_Blog_Speech_Synthesis_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'add_audio_to_post_content' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    AiXplain_Recite_TTS_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
