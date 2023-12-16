<?php
/**
 * Plugin Name: WooCommerce Mix and Match -  Quickview
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 * Version: 2.0.0
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
		const VERSION = '2.0.0';

		const REQ_MNM_VERSION = '2.6.0';

		/**
		 * WC_MNM_Quickview Constructor
		 *
		 * @access 	public
		 * @return 	WC_MNM_Quickview
		 */
		public static function init() {


			// Quietly quit if Mix and Match is not active or below required version.
			if ( ! function_exists( 'wc_mix_and_match' ) || version_compare( wc_mix_and_match()->version, self::REQ_MNM_VERSION ) < 0 ) {
				return false;
			}


			// Declare HPOS compatibility.
			add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_features_compatibility' ) );

			// Register Scripts.
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );

			// Display Scripts.
			add_action( 'woocommerce_mix-and-match_add_to_cart', array( __CLASS__, 'load_scripts' ) );

			// Preload REST Responses.
			add_action( 'woocommerce_mix-and-match_add_to_cart', [ __CLASS__, 'preload_response' ] );
			add_action( is_admin() ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts', array( __CLASS__, 'enqueue_asset_data' ), 0 );

		}





		}

		/*-----------------------------------------------------------------------------------*/
		/* Core Compat */
		/*-----------------------------------------------------------------------------------*/


		/**
		 * Declare HPOS (Custom Order tables) compatibility.
		 *
		 * @since 2.0.0
		 */
		public static function declare_features_compatibility() {

			if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				return;
			}

			// High Performance Order Storage (HPOS).
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );

			// Cart and Checkout Blocks.
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', plugin_basename( __FILE__ ), true );
			
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

			$style_dependencies  = array( 'wp-components', 'wc-mnm-frontend' );

			wp_enqueue_style( 'wc-mnm-quickview', $style_url, $style_dependencies, $style_version );
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
			$script_path = 'assets/dist/frontend/quick-view.js';
			$script_url  = trailingslashit( plugins_url( '/', __FILE__ ) ) . $script_path;

			$script_asset_path = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'assets/dist/frontend/quick-view.asset.php';
			$script_asset      = file_exists( $script_asset_path )
				? require $script_asset_path
				: array(
					'dependencies' => array(),
					'version'      => WC_Mix_and_Match()->get_file_version( trailingslashit( plugin_dir_path( __FILE__ ) ) . $script_path ),
				);

			wp_register_script(
				'wc-mnm-quickview',
				$script_url,
				$script_asset[ 'dependencies' ],
				$script_asset[ 'version' ],
				true
			);

			wp_script_add_data( 'wc-add-quickview', 'strategy', 'defer' );

		}

		/**
		 * Load the script anywhere the MNN add to cart button is displays
		 */
		public static function load_scripts() {

			wp_enqueue_script( 'wc-mnm-quickview' );
				
			add_action( 'wc_mnm_child_item_details', array( __CLASS__, 'display_trigger_button' ), 69, 2 );

			add_action( 'wp_footer', function() {
				echo '<div id="wc-mix-and-match-quick-view-modal"></div>';
			});

		}


		/*-----------------------------------------------------------------------------------*/
		/*  Preloading                                                                       */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Stash product ID for lazy preloading.
		 *
		 * @return object
		 */
		public static function preload_response() {
		
			global $product;

			$preloads = WC_MNM_Helpers::cache_get( 'wcMNMQuickViewPreloads' );

			if ( is_array( $preloads ) ) {
				$preloads[] = $product->get_id();
				WC_MNM_Helpers::cache_set( 'wcMNMQuickViewPreloads', $preloads );
			} elseif ( null === $preloads ) {
				$preloads = [ $product->get_id() ];
			}

			WC_MNM_Helpers::cache_set( 'wcMNMQuickViewPreloads', $preloads );

		}

		/**
		 * Preload all variations into WC settings.
		 *
		 * @return object
		 */
		public static function enqueue_asset_data() {

			$preloads = WC_MNM_Helpers::cache_get( 'wcMNMQuickViewPreloads' );

			if ( ! empty( $preloads ) && is_array( $preloads ) ) {

				$data = [];

				$assets = Automattic\WooCommerce\Blocks\Package::container()->get( Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class );

				foreach ( $preloads as $product_id ) {

					$rest_route = '/wc/store/v1/products/' . $product_id ;

					$assets->hydrate_api_request( $rest_route );

					$rest_preload_api_requests = rest_preload_api_request( [], $rest_route );

					$data[$product_id] = $rest_preload_api_requests[$rest_route]['body']['extensions']->mix_and_match ?? [];

				}

				$assets->add( 'wcMNMQuickViewPreloads', $data );

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




	} // End class: do not remove or there will be no more guacamole for you.

endif; // end class_exists check

// Launch the whole plugin.
add_action( 'plugins_loaded', array( 'WC_MNM_Quickview', 'init' ), 20 );