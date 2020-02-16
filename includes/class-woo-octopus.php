<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://apkee.hk/kedi
 * @since      1.0.0
 *
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/includes
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
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/includes
 * @author     paulus <apkee.hk@gmail.com>
 */
class Woo_Octopus {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Octopus_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'WOO_OCTOPUS_VERSION' ) ) {
			$this->version = WOO_OCTOPUS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-octopus';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->loader->add_action('plugins_loaded', $this, 'load_gateways', 11);

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Octopus_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Octopus_i18n. Defines internationalization functionality.
	 * - Woo_Octopus_Admin. Defines all hooks for the admin area.
	 * - Woo_Octopus_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require plugin_dir_path(dirname(__FILE__)) . 'includes/api/api-helpers.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-octopus-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-octopus-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-octopus-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-octopus-public.php';

		$this->loader = new Woo_Octopus_Loader();

	}

	public function load_gateways() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/payment-methods/class-woo-octopus-gateway-offline.php';
		$this->define_gateways();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Octopus_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Octopus_i18n();

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

		$plugin_admin = new Woo_Octopus_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action('manage_shop_order_posts_custom_column', $plugin_admin, 'add_reference_number_to_order_list', 11, 1);
		$this->loader->add_action('woocommerce_admin_order_data_after_order_details', $plugin_admin, 'add_reference_number_to_order_detail');

		// Filters
		$this->loader->add_filter('woocommerce_payment_gateways', $plugin_admin, 'add_gateways');
		$this->loader->add_filter('plugin_action_links_' . plugin_basename(__FILE__), $plugin_admin, 'add_plugin_links');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_Octopus_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Shortcodes
		$this->loader->add_shortcode('octopus_reference_number_input', $plugin_public, 'octopus_reference_number_input_shortcode');
	}

	public function define_gateways() {
		$octopus_offline = new Woo_Octopus_Gateway_Offline();
		add_action('woocommerce_update_options_payment_gateways_' . $octopus_offline->id, array($octopus_offline, 'process_admin_options'));
		add_action('woocommerce_thankyou_' . $octopus_offline->id, array($octopus_offline, 'thankyou_page'));
		add_action('woocommerce_email_before_order_table', array($octopus_offline, 'email_instructions'), 10, 3);
		add_action('woocommerce_order_details_before_order_table', array($octopus_offline, 'order_detail_reference_number_input'), 10, 1);
		add_action('woocommerce_get_order_item_totals', array($octopus_offline, 'order_detail_instructions'), 10, 3);
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
	 * @return    Woo_Octopus_Loader    Orchestrates the hooks of the plugin.
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
