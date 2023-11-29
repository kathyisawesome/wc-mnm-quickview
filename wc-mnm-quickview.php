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

		// Attach form hooks.
		add_action( 'wc_mnm_quick_view_before_single_product', array( __CLASS__, 'template_hooks' ) );

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

		$suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '': '.min';

		// Styles.
		$style_path    = 'assets/css/frontend/quickview' . $suffix . '.css';
		$style_url     = trailingslashit( plugins_url( '/', __FILE__ ) ) . $style_path;
		$style_version = WC_Mix_and_Match()->get_file_version( trailingslashit( plugin_dir_path( __FILE__ ) ) . $style_path, self::VERSION );

		$style_dependencies  = array( 'woocommerce_prettyPhoto_css' );

		wp_enqueue_style( 'wc-mnm-quickview', $style_url, array( 'wc-mnm-frontend' ), $style_version );
		wp_style_add_data( 'wc-mnm-quickview', 'rtl', 'replace' );

		if ( $suffix ) {
			wp_style_add_data( 'wc-mnm-quickview', 'suffix', '.min' );
		}

		$script_dependencies = array( 'jquery', 'prettyPhoto', 'wc-single-product', 'wc-add-to-cart-variation', 'wc-add-to-cart-mnm' );

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

		// Scripts.
		$script_path    = 'assets/js/frontend/quickview' . $suffix . '.js';
		$script_url     = trailingslashit( plugins_url( '/', __FILE__ ) ) . $script_path;
		$script_version = WC_Mix_and_Match()->get_file_version( trailingslashit( plugin_dir_path( __FILE__ ) ) . $script_path, self::VERSION );

		wp_register_script( 'wc-mnm-quickview', $script_url, $script_dependencies, $script_version, true );

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

		if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
			wp_enqueue_script( 'wc-mnm-quickview' );
			add_action( 'wc_mnm_child_item_details', array( __CLASS__, 'display_trigger_button' ), 66, 2 );
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
	 * @param obj WC_MNM_Child_Item $child_item of child item
	 * @param obj WC_Mix_and_Match $container_product the parent container
	 */
	public static function display_trigger_button( $child_item, $container_product ) {

		wc_get_template(
			'single-product/mnm/quickview/button.php',
			array(
				'child_item'        => $child_item,
				'child_product'     => $child_item->get_product(),
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

	/**
	 * Modify quickview template.
	 * 
	 * @since 2.0.0
	 */
	public static function template_hooks() {
		// Change form action to avoid redirect to product page.
		add_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	}

} //end class: do not remove or there will be no more guacamole for you

endif; // end class_exists check

// Launch the whole plugin.
add_action( 'wc_mnm_loaded', array( 'WC_MNM_Quickview', 'init' ) );
