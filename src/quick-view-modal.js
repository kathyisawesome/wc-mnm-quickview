/**
 * External dependencies
 */
import { Interweave } from 'interweave';
import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { _x } from '@wordpress/i18n';

import { useState, useEffect } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';


import { useContainer, ContainerProvider } from './context/container';

/**
 * Fetch a product object from the Store API.
 *
 * @param {number} containerId Id of the parent product to retrieve.
 */
function getContainer( containerId ) {

    try {
        // Only attempt to resolve if there's a product ID here.
        if ( containerId ) {

            const preloadedData = getSetting(
                'wcMNMQuickViewPreloads',
                []
            );

            if ( preloadedData.hasOwnProperty( containerId ) ) {

                return preloadedData[containerId];

            }

           
        }
    } catch ( error ) {
        // @todo: Handle an error here eventually.
        console.error( error );
    }
}


/**
 * Fetch a product object from the Store API.
 *
 * @param {obj} container The container response.
 * @param {string} childId Id of the product to retrieve.
 */
function getChildItem( container, childId ) {

    try {
        // Only attempt to resolve if there's a product ID here.
        if ( childId && container.child_items ) {

            let foundItem = container.child_items.find(
                ( obj ) => obj.child_item_id == childId
            );

            return foundItem;
           
        }
    } catch ( error ) {
        // @todo: Handle an error here eventually.
        console.error( error );
    }
}

const QuickViewModal = () => {

    // State to manage modal open/close
    const [isOpen, setOpen] = useState(false);

    // State to manage parent product.
    const [container, setContainer] = useState(false);

    // State to manage child item.
    const [childItem, setChildItem] = useState(false);
    
    // Function to close the modal
    const closeModal = () => {
        setOpen(false);
    };
    
    useEffect(() => {

        // Listen for clicks on the entire window
        document.addEventListener('click', function (event) {

            // Ignore elements without the .wc-mnm-quick-view-button class
            if ( ! event.target.closest('.wc-mnm-quick-view-button') ) return;
            
            // Run your code...
            event.preventDefault();

            let containerId = event.target.getAttribute('data-container_id' );

            let product = getContainer( containerId );

            setContainer( product );

            let childId = event.target.getAttribute('data-item_id');    
            let item = getChildItem(product, childId);

            setChildItem(item);

            setOpen(true);

        });

    }, []);


    const image = childItem && childItem.images.length ? childItem.images[ 0 ] : {};

    return (
         
         <>
            { isOpen && (
                <Modal onRequestClose={ closeModal }>

                    <div className="wp-block-columns wp-block-columns is-layout-flex  wp-block-columns-is-layout-flex">
                        
                        <div className="wp-block-column" style={{'flexBasis':'40%'}}>

                            <img
                                className={ `wc-block-components-product-image wp-image-${image.id}` }
                                src={ image.src }
                                alt={ decodeEntities( image.alt ) }
                            />

                        </div>

                        <div className="wp-block-column wp-block-column is-layout-flow wp-block-column-is-layout-flow" style={{'flexBasis':'60%'}}>

                            <h2 className="wp-block-post-title" >
                                <Interweave content={ childItem.name } />
                            </h2>

                            { container.priced_per_product && (
                                <p className="wc-mnm-block-child-item__product-price">
                                    <Interweave content={ childItem.price_html } />
                                </p>
                            ) }

                            <p className="wc-mnm-block-child-item__product-description">
                                <Interweave content={ childItem.short_description } />
                            </p>
                        </div>
                    
                    </div>    

                </Modal>
            ) }
        </>

    );
};


export default QuickViewModal;
