<?php
/**
 * Plugin Name: WooCommerce Mix and Match -  Quickview
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 * Version: 1.0.0-beta-1
 * Description: Add pop-up lightbox for child product details. 
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com/
 * Text Domain: wc-mnm-lightbox
 * Domain Path: /languages
 *
 * Copyright: © 2020 Kathy Darling
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */



/**
 * The Main WC_MNM_Quickview class
 **/
if ( ! class_exists( 'WC_MNM_Quickview' ) ) :

class WC_MNM_Quickview {

	/**
	 * constants
	 */
	CONST VERSION = '1.0.0-beta-1';


	/**
	 * WC_MNM_Quickview Constructor
	 *
	 * @access 	public
     * @return 	WC_MNM_Quickview
	 */
	public static function init() {

		// Declare HPOS compatibility.
		add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_hpos_compatibility' ) );

		// Register Scripts.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );

		// Display Scripts.
		add_action( 'woocommerce_mix-and-match_add_to_cart', array( __CLASS__, 'load_scripts' ) );

		// Show a product via API.
		add_action( 'wc_ajax_wc-mnm-quickview', array( __CLASS__, 'modal' ) );

    }

	/*-----------------------------------------------------------------------------------*/
	/* Core Compat */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Declare HPOS (Custom Order tables) compatibility.
	 *
	 * @since 2.0.0
	 */
	public static function declare_hpos_compatibility() {

		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );
	}

	/*-----------------------------------------------------------------------------------*/
	/* Scripts and Styles */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Register styles and scripts.
	 */
	public static function register_scripts() {

		$script_dependencies = array( 'jquery', 'prettyPhoto', 'wc-single-product', 'wc-add-to-cart-variation', 'wc-add-to-cart-mnm' );
		$style_dependencies  = array( 'woocommerce_prettyPhoto_css' );

		// Load gallery scripts on product pages only if supported.
		if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
			$script_dependencies[] = 'zoom';
		}
		if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
			$script_dependencies[] = 'flexslider';
		}
		if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
			$script_dependencies[] = 'photoswipe-ui-default';
		}

		$plugin_url = untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) );

		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		wp_register_script( 'wc-mnm-quickview', $plugin_url . '/assets/js/frontend/quickview' . $suffix . '.js', $script_dependencies, self::VERSION, true );


		wp_localize_script(
			'wc-mnm-quickview',
			'WC_MNM_QUICKVIEW_PARAMS',
			array(
				'ajax_url' => WC_AJAX::get_endpoint( 'wc-mnm-quickview&ajax=true&product_id=%%product_id%%' ),
			)
		);

	}

	/**
	 * Load the script anywhere the MNN add to cart button is displays
	 */
	public static function load_scripts() {

		if( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
			wp_enqueue_script( 'wc-mnm-quickview' );
			add_action( 'woocommerce_mnm_child_item_details', array( __CLASS__, 'display_trigger_button' ), 66, 2 );
			add_action( 'wp_footer', 'woocommerce_photoswipe' );
		}

	}

	/*-----------------------------------------------------------------------------------*/
	/* Display                                                                           */
	/*-----------------------------------------------------------------------------------*/


	/**
	 *
	 * Replace default filter with new one.
	 * 
	 * @param WC_Product $child_product
	 * @param WC_Product_Mix_and_Match $container_product
	 */
	public static function display_trigger_button( $child_product, $container_product ) {

		wc_get_template(
			'single-product/mnm/quickview/button.php',
			array(
				'child_product' => $child_product,
				'container_product' => $container_product,
			),
			'',
			untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/'
		);

	}

	/**
	 * Display ajax content.
	 */
	public static function modal() {
		global $post;

		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;

		if ( $product_id ) {

			// Get product ready.
			$post = get_post( $product_id );

			setup_postdata( $post );

			wc_get_template(
				'single-product/mnm/quickview/modal.php',
				array(),
				'',
				untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/'
			);

		}

		exit;
	}


} //end class: do not remove or there will be no more guacamole for you

endif; // end class_exists check

// Launch the whole plugin.
add_action( 'woocommerce_mnm_loaded', array( 'WC_MNM_Quickview', 'init' ) );
