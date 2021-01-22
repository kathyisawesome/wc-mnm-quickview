# WooCommerce Mix and Match - Quickview. 

### What's This?

Experimental mini-extension for [WooCommerce Mix and Match](https://woocommerce.com/products/woocommerce-mix-and-match-products//) that adds lightbox support for the child products. 

![Recording of a table listing of t-shirts. When clicking on the first one's thumbnail a lightbox appears with a larger version of that image.](https://user-images.githubusercontent.com/507025/80630262-2ea71380-8a11-11ea-92de-c85490e34d89.gif)

### Usage

Your theme must support WooCommerce 3.0-style galleries. If it does not, then the thumbnails will not launch a lightbox. In order to declare support for WooCommerce and all galleries you can use the following snippet:

```
/**
 * Declare support for WooCommerce and WooCommerce 3.0 Galleries
 */
function kia_woocommerce_support() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'kia_woocommerce_support' ); 
```

There is nothing else to configure. Activating the plugin automatically adds a lightbox to every child image _if_ your theme supports `wc-product-gallery-lightbox`.

### Important

1. This is provided as is and does not receive priority support.
2. Please test thoroughly before using in production.
