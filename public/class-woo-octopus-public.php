<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://apkee.hk/kedi
 * @since      1.0.0
 *
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/public
 * @author     paulus <apkee.hk@gmail.com>
 */
class Woo_Octopus_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Woo_Octopus_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Octopus_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-octopus-public.css', array(), $this->version, 'all' );

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
		 * defined in Woo_Octopus_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Octopus_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-octopus-public.js', array( 'jquery' ), $this->version, false );

		/**
		 * 參考：https://philkurth.com.au/articles/pass-data-php-javascript-wordpress/
		 * 準備 parameters 過俾 JavaScript 用
		 */
		/*
		$media_selector_data = [
			'media_selector_text_id'	=> $this->plugin_name . '_media_selector_text',
			'media_selector_button_id'	=> 'upload_image_button',
		];
		wp_localize_script( $this->plugin_name, 'media_selector_data', $media_selector_data);
		wp_enqueue_script( $this->plugin_name );
		*/
		
	}

	/**
	 * Load public area templates
	 * 
 	 * @since      1.0.0
	 * @param string $template_name
	 * @return string template content
	 */
	private function load_template($template_name, $echo=false) {
		if (file_exists(plugin_dir_path( __FILE__ ) . 'partials/' . $template_name)) {
			return woo_octopus_load_template(plugin_dir_path( __FILE__ ) . 'partials/' . $template_name, $echo);
		} else {
			return false;
		}
	}

	/**
	 * Shortcode for octopus reference number input
	 * 
     * @since      1.0.0
     * @param	array $atts
	 * @param string $content
	 * @param string $tag
	 * @return string reference number display
	 * 
	*/
	public function octopus_reference_number_input_shortcode($atts=[], $content=null, $tag='') {
		$atts = array_change_key_case((array)$atts, CASE_LOWER);
		$form_atts = shortcode_atts([
			'order_id' => '',
		], $atts, $tag);

		if (!$form_atts['order_id']) {
			return false;
		}

		$order = wc_get_order($form_atts['order_id']);
		$octopus_reference_no = $order->get_meta('_octopus_reference_no');
		if (!$octopus_reference_no) {
			// Handle reference number submission
			if (isset($_POST['octopus_reference_number']) && $_POST['octopus_reference_number'] && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'update_octopus_reference_number')) {
				$octopus_reference_no = sanitize_text_field($_POST['octopus_reference_number']);
				$order->update_meta_data('_octopus_reference_no', $octopus_reference_no);
				$order->save();
				unset($_POST['octopus_reference_number']);
			} else {
				return $this->load_template('woo-octopus-reference-number.php');
			}
		}
		return '';
	}
}
