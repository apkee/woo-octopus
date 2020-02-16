<?php
/**
 * Define Octopus offline payment gateway
 * 
 * @since      1.0.0
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/includes
 */
class Woo_Octopus_Gateway_Offline extends WC_Payment_Gateway {
    protected $account;

    public function __construct() {
        $this->id                 = 'octopus_offline_gateway';
        $this->icon               =  plugin_dir_url( dirname( __DIR__ ) ) . 'public/images/octopus-icon-small.png';
        $this->has_fields         = false;
        $this->method_title       = __( 'Octopus Payments', 'woocommerce-octopus' );
        $this->method_description = __( "Allows Octopus payments. If you need any help on customized Octopus integration, feel free to contact us at <a href='mailto:info@apkee.hk'>info@apkee.hk</a>", 'woocommerce-octopus' );
        $this->account = get_option('woocommerce_octopus_offline_gateway_account');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title              = $this->get_option( 'title' );
        $this->description        = $this->get_option( 'description' );
        $this->instructions       = $this->get_option( 'instructions', $this->description );
        $this->enabled            = $this->get_option( 'enabled' );
        $this->qrcode             = $this->get_option( 'qrcode' );
    }

    /**
     * @since      1.0.0
     * Initialize Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $this->form_fields = apply_filters( 'woo_octopus_offline_form_fields', array(
        'enabled' => array(
            'title'   => __( 'Enable/Disable', 'woo-octopus' ),
            'type'    => 'checkbox',
            'label'   => __( 'Enable Octopus Payments', 'woo-octopus' ),
            'default' => 'yes'
        ),
        
        'title' => array(
            'title'       => __( 'Title', 'woo-octopus' ),
            'type'        => 'text',
            'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'woo-octopus' ),
            'default'     => __( 'Octopus Payments', 'woo-octopus' ),
            'desc_tip'    => true,
        ),
        
        'description' => array(
            'title'       => __( 'Description', 'woo-octopus' ),
            'type'        => 'textarea',
            'description' => __( 'Payment method description that the customer will see on your checkout.', 'woo-octopus' ),
            'default'     => __( 'Please remit payment to the following Octopus Account:', 'woo-octopus' ),
            'desc_tip'    => true,
        ),
        
        'instructions' => array(
            'title'       => __( 'Instructions', 'woo-octopus' ),
            'type'        => 'textarea',
            'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woo-octopus' ),
            'default'     => __( 'Please provide your reference number in Order Detail page after remitted payment through Octopus.', 'woo-octopus' ),
            'desc_tip'    => true,
        ),
        /*
        'qrcode' => array(
            'title'       => __( 'Octopus Merchant QR Code', 'woo-octopus' ),
            'type'        => 'text',
            'description' => __( 'Your Octopus merchant QR Code image path.', 'woo-octopus' ),
            'desc_tip'    => true,
        ),
        */
        //* 參考：https://www.ibenic.com/additional-woocommerce-settings-pages-for-a-payment-gateway/
        'qrcode' => array(
            'id'    => 'media_selector',
            'type'  => 'media_selector',
            'title' => __( 'Octopus Merchant QR Code', 'woo-octopus' ),
            'description' => __( 'Your Octopus merchant QR Code image path.', 'woo-octopus' ),
            'desc_tip'    => true,
        ),
        ));
    }

    /**
     * @since      1.0.0
     * Payment description at checkout page
     */
    public function payment_fields() {
        $description = $this->get_option( 'description' );      //get_description();
        $qrcode = $this->get_option( 'qrcode' );

        if ( $description ) {
                echo wpautop( wptexturize( __( $description, 'woo-octopus' ) ) );
            }
        if ( $qrcode ) {
            ?>
            <img src='<?php echo $qrcode; ?>' style='width:128px;' /></br>
            <?php
        }
        echo '<div class="payment-octopus-tooltip">'. __( 'How to use Octopus payment?', 'woo-octopus' ) .'<img class="payment-octopus-tooltip-img" src="' . plugin_dir_url( dirname( __DIR__ ) ) . 'public/images/octopus-instruction.jpg"></img></div>';
    }

    /**
     * @since      1.0.0
         * Add content to the order received page.
         */
    public function thankyou_page() {
        $instructions = $this->instructions;
        if ( $instructions ) {
            echo wpautop( wptexturize( __( $instructions, 'woo-octopus' ) ) );
        }
    }

    /**
     * @since      1.0.0
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
            echo wpautop( wptexturize( __( $this->instructions, 'woo-octopus' ) ) ) . PHP_EOL;
        }
    }

    /**
     * @since      1.0.0
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        $order->update_status( 'on-hold', __( 'Awaiting Octopus payments', 'woo-octopus' ) );
        $order->reduce_order_stock();
        WC()->cart->empty_cart();
        
        return array(
            'result' 	=> 'success',
            'redirect'	=> $this->get_return_url( $order )
        );
    }

    /**
     * @since      1.0.0
     * Add payment instruction and reference number fields to order detail page
     *
     * @param array $total_rows
     * @param WC_Order $order
     * @param mixed $tax_display
     * @return array
     */
    public function order_detail_instructions($total_rows, $order, $tax_display) {
        $payment_method = $order->get_payment_method();
        $order_status = $order->get_status();

        if ($payment_method == $this->id) {
            $reference_no = $order->get_meta('_octopus_reference_no');
            $payment_description = array(
                'label' => __('Payment description:', 'woo-octopus'),
                'value' => __( $this->description, 'woo-octopus' )
            );

            $payment_method_pos = array_search('payment_method', array_keys($total_rows));
            if (!$reference_no) {
                $order->read_meta_data(true);
                $reference_no = $order->get_meta('_octopus_reference_no');
            }

            $reference_no = !$reference_no ? __('Not submitted', 'woo-octopus') : $reference_no;
            $octopus_reference_display = array(
                'label' => __('Octopus reference number:', 'woo-octopus'),
                'value' => $reference_no
            );
            
            if ( $reference_no ) {
                $total_rows = array_slice($total_rows, 0, $payment_method_pos+1, true) + array('octopus_reference_no' => $octopus_reference_display) + array_slice($total_rows, $payment_method_pos+1, count($total_rows) - 1, true);
            }
            else {
            $total_rows = array_slice($total_rows, 0, $payment_method_pos+1, true) + array('payment_description' => $payment_description) + array('octopus_reference_no' => $octopus_reference_display) + array_slice($total_rows, $payment_method_pos+1, count($total_rows) - 1, true);
            }
        }
        
        return $total_rows;
    }

    /**
     * @since      1.0.4
     * Add Octpus reference number submit form to order detail page
     *
     * @param array $total_rows
     * @param WC_Order $order
     * @param mixed $tax_display
     * @return array
     */
    public function order_detail_reference_number_input($order) {
        $payment_method = $order->get_payment_method();
        $order_status = $order->get_status();
        if ($payment_method == $this->id) {
            $reference_no = $order->get_meta('_octopus_reference_no');
            if (!$reference_no) {
                echo do_shortcode('[octopus_reference_number_input order_id=' . $order->get_id() . ']');
            }
        }
    }

    /** 自定義 setting page 加 Media Selector
     * 參考：https://www.ibenic.com/additional-woocommerce-settings-pages-for-a-payment-gateway/
     *      https://docs.woocommerce.com/wc-apidocs/source-class-WC_Settings_API.html#398-439
     * 
     * Screen button Field
     */
    public function generate_media_selector_html( $key, $data ) { 
        $field_key = $this->get_field_key( $key );
        $defaults  = array(
            'title'             => '',
            'disabled'          => false,
            'class'             => '',
            'css'               => '',
            'placeholder'       => '',
            'type'              => 'text',
            'desc_tip'          => false,
            'description'       => '',
            'custom_attributes' => array(),
        );

        $data = wp_parse_args( $data, $defaults );

		/**
		 * 參考：https://philkurth.com.au/articles/pass-data-php-javascript-wordpress/
		 * 準備 PHP parameters 過俾 JavaScript 用
		 */
        $my_saved_attachment_post_id = get_option( 'media_selector_attachment_id', 0 );
		$media_selector_data = [
			'media_selector_text_id'	            => $field_key,      // 要同個 field id 一樣先可以 save 到啲 input
            'media_selector_button_id'	            => 'upload_image_button',
            'media_selector_attachment_id'          => $my_saved_attachment_post_id,
            'media_selector_image_preview_id'       => 'image_preview',
            'media_selector_image_attachment_id'    => 'image_attachment_id',
		];

        ob_start();
        ?>
        <script type="application/json" id="media_selector_data"><?= json_encode( $media_selector_data, JSON_UNESCAPED_SLASHES ) ?></script>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
            </th>
            <td colspan="2" class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                    <input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>"
                        type="text" 
                        name="<?php echo esc_attr( $field_key ); ?>" 
                        id="<?php echo esc_attr( $field_key ); ?>" 
                        class="input-text regular-input"
                        style="<?php echo esc_attr( $data['css'] ); ?>" 
                        value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" 
                        placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" 
                        <?php disabled( $data['disabled'], true ); ?> 
                        <?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?> />
                    <input id="upload_image_button" type="button" class="button" value="<?php _e( 'Select image' ); ?>" />
                    <input type='hidden' name='image_attachment_id' id='image_attachment_id' value='<?php echo get_option( 'media_selector_attachment_id' ); ?>'>
                    <div class='image-preview-wrapper'>
                        <img id='image_preview' src="<?php echo esc_attr( $this->get_option( $key ) ); ?>" style='height:100px;margin:4px 4px;'>
                    </div>
                </fieldset>
            </td>
        </tr>

        <?php

        return ob_get_clean();
    
    }

    /**
     * Validate Text Field.
     *
     * Make sure the data is escaped correctly, etc.
     *
     * @param  string $key Field key.
     * @param  string $value Posted Value.
     * @return string
     */
    public function validate_media_selector_field( $key, $value ) {
        $value = is_null( $value ) ? '' : $value;
        return wp_kses_post( trim( stripslashes( $value ) ) );
    }

    /** 參考：https://www.ibenic.com/additional-woocommerce-settings-pages-for-a-payment-gateway/
     * Redefining how options are saved for this gateway.
     * If we are on a second screen, we will save the other fields.
     * If we are not on the second screen, save the original fields.
     */
    public function process_admin_options() {
        if( isset( $_GET['screen'] ) && '' !== $_GET['screen'] ) {
            WC_Admin_Settings::save_fields( $this->form_fields );
        } else {
            parent::process_admin_options();
        }
    }
}