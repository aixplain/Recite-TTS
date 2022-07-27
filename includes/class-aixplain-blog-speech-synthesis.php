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
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/includes
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
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class Aixplain_Blog_Speech_Synthesis {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Aixplain_Blog_Speech_Synthesis_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
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
		if ( defined( 'AIXPLAIN_BLOG_SPEECH_SYNTHESIS_VERSION' ) ) {
			$this->version = AIXPLAIN_BLOG_SPEECH_SYNTHESIS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'aixplain-blog-speech-synthesis';

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
	 * - Aixplain_Blog_Speech_Synthesis_Loader. Orchestrates the hooks of the plugin.
	 * - Aixplain_Blog_Speech_Synthesis_i18n. Defines internationalization functionality.
	 * - Aixplain_Blog_Speech_Synthesis_Admin. Defines all hooks for the admin area.
	 * - Aixplain_Blog_Speech_Synthesis_Public. Defines all hooks for the public side of the site.
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
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libraries/wp-background-processing/wp-background-processing.php';
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libraries/wp-background-processing/wp-background-processing.php';
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aixplain-blog-speech-synthesis-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aixplain-blog-speech-synthesis-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-aixplain-blog-speech-synthesis-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-aixplain-blog-speech-synthesis-public.php';

		/**
		 * The class responsible for core functionalities of plugin: processing the posts and generating audio...
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-aixplain-blog-speech-synthesis-processor.php';

		$this->loader = new Aixplain_Blog_Speech_Synthesis_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Aixplain_Blog_Speech_Synthesis_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Aixplain_Blog_Speech_Synthesis_i18n();

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

		$plugin_admin = new Aixplain_Blog_Speech_Synthesis_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_synthesize_all_posts', $plugin_admin, 'register_synthesize_action' );
		$this->loader->add_action( 'delete_attachment', $plugin_admin, 'on_attachment_delete' );
		$this->loader->add_filter( 'manage_post_posts_columns', $plugin_admin, 'add_column_to_posts' );
		$this->loader->add_action( 'manage_post_posts_custom_column', $plugin_admin, 'add_content_to_posts_column' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_synthesize_single_post', $plugin_admin, 'register_synthesize_post_action' );
		$this->loader->add_action( 'wp_ajax_aixplain_bss_unsynthesize_single_post', $plugin_admin, 'register_unsynthesize_post_action' );
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

		$plugin_public = new Aixplain_Blog_Speech_Synthesis_Public( $this->get_plugin_name(), $this->get_version() );

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
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Aixplain_Blog_Speech_Synthesis_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
