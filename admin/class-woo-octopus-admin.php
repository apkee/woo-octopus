<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://apkee.hk/kedi
 * @since      1.0.0
 *
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/admin
 * @author     paulus <apkee.hk@gmail.com>
 */
class Woo_Octopus_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Woo_Octopus_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Octopus_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-octopus-admin.css', array(), $this->version, 'all' );

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
		 * defined in Woo_Octopus_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Octopus_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-octopus-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add Octopus gateways to WC gateway list
	 * 
	 * @since 1.0.0
	 * @param array $gateways all available WC gateways
	 * @return array $gateways all WC gateways + Octopus gateways
	 */
	public function add_gateways($gateways) {
		$gateways[] = 'Woo_Octopus_Gateway_Offline';
		return $gateways;
	}

	/**
	 * Adds plugin page links
	 * 
	 * @since 1.0.0
	 * @param array $links all plugin links
	 * @return array $links all plugin links + configuration link
	 */
	public function add_plugin_links($links) {
		$plugin_links = array(
			'<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=offline_gateway') . '">' . __('Settings', 'woo-octopus') . '</a>'
		);
		
		return array_merge($plugin_links, $links);
	}

	/**
	 * Load admin area templates
	 * 
	 * @since      1.0.0
	 * @param string $template_name
	 * @return string template content
	 */
	private function load_template($template_name, $params=array(), $echo=false) {
		if (file_exists(plugin_dir_path( __FILE__ ) . 'partials/' . $template_name)) {
			return woo_octopus_load_template(plugin_dir_path( __FILE__ ) . 'partials/' . $template_name, $params, $echo);
		} else {
			return false;
		}
	}

	/**
	 * Add octopus reference number to order list
	 * 
	 * @since 1.0.0
	 * @param array $column
	 * @return array $links all plugin links + configuration link
	 */
	public function add_reference_number_to_order_list($column) {
		global $post;
	
		if ($column == 'order_number') {
			$order = wc_get_order($post->ID);
			$payment_method = $order->get_payment_method();
			if (preg_match('/^octopus_/', $payment_method) === 1) {
				$reference_no = $order->get_meta('_octopus_reference_no');
				$this->load_template('woo-octopus-reference-number.php', array('reference_no' => $reference_no), true);
			}
		}
	}

	/**
	 * Add octopus reference number to order detail
	 * 
	 * @since 1.0.0
	 * @param array $column
	 * @return array $links all plugin links + configuration link
	 */
	public function add_reference_number_to_order_detail($order) {
		$payment_method = $order->get_payment_method();
		if (preg_match('/^octopus_/', $payment_method) === 1) {
			$reference_no = $order->get_meta('_octopus_reference_no');
			$this->load_template('woo-octopus-reference-number.php', array('reference_no' => $reference_no), true);
		}
	}

}
