/**
 * External dependencies
 */
import { createRoot, render } from '@wordpress/element';
import QuickViewModal from './quick-view-modal';

const target = document.getElementById( 'wc-mix-and-match-quick-view-modal' );

if ( target ) {

    if ( createRoot ) {
        createRoot( target ).render( <QuickViewModal /> );
    } else {
        render( <QuickViewModal />, target );
    }
}