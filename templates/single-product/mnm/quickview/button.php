<?php
/**
 * Mix and Match Item Quickview trigger button
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/mnm/quickview/button.php.
 *
 * HOWEVER, on occasion WooCommerce Mix and Match will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Kathy Darling
 * @package WooCommerce Mix and Match/Templates
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

echo apply_filters(
	'wc_mnm_child_quick_view_button',
	sprintf(
		'<button type="button" title="%s" data-product_id="%s" class="wc-mnm-quick-view-button button"><span></span>%s</button>',
		esc_attr( $child_product->get_title() ),
		$child_product->get_id(),
		esc_html__( 'Quick View', 'wc-mnm-quickview' )
	)
);
