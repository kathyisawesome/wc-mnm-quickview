jQuery(
	function( $ ) {
		$.fn.prettyPhoto(
			{
				social_tools: false,
				theme: 'pp_woocommerce pp_woocommerce_quick_view',
				opacity: 0.8,
				modal: false,
				horizontal_padding: 50,
				default_width: '90%',
				default_height: '90%',
				changepicturecallback: function() {
					$( '.wc-mnm-quick-view .woocommerce-product-gallery' ).wc_product_gallery();
					$( '.wc-mnm-quick-view .variations_form' ).wc_variation_form();
					$( '.wc-mnm-quick-view .variations_form' ).trigger( 'wc_variation_form' );
					$( '.wc-mnm-quick-view .variations_form .variations select' ).change();
					$( 'body' )
						.trigger( 'quick-view-displayed' )
						.trigger( 'wc_currency_converter_calculate' );
				}
			}
		);
		$( document ).on(
			'click',
			'.wc-mnm-quick-view-button',
			function() {
				var product_id = $( this ).data( 'product_id' );

				if ( product_id ) {
					$.prettyPhoto.open(
						decodeURIComponent(
							WC_MNM_QUICKVIEW_OPTIONS.ajax_url.replace(
								'%%product_id%%',
								product_id
							)
						)
					);

					return false;
				}

				return true;
			}
		);
	}
);
