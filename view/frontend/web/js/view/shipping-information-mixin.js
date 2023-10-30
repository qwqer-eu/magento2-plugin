/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function (wrapper, quote) {
    'use strict';

    var mixin = {

        getShippingMethodTitle: function () {
            var shippingMethod = quote.shippingMethod(),
                shippingMethodTitle = '';

            if (!shippingMethod) {
                return '';
            }

            shippingMethodTitle = shippingMethod['carrier_title'];

            if (typeof shippingMethod['method_title'] !== 'undefined') {
                shippingMethodTitle += ' - ' + shippingMethod['method_title'];
            }

            if (typeof shippingMethod.carrier_code !== 'undefined'
                && (shippingMethod.carrier_code == 'qwqer'
                    || shippingMethod.carrier_code == 'qwqer_door'
                    || shippingMethod.carrier_code == 'qwqer_parcel')
            ) {
                let shippingAddress = quote.shippingAddress();
                let extensionAttributesCheckoutConfig = window.checkoutConfig.extension_attributes;
                if (shippingAddress.extension_attributes != undefined
                    && shippingAddress.extension_attributes.qwqer_address != undefined
                    && shippingAddress.extension_attributes.qwqer_address != ''
                ) {
                    shippingMethodTitle += " (" + shippingAddress.extension_attributes.qwqer_address + ")";
                } else if (extensionAttributesCheckoutConfig && extensionAttributesCheckoutConfig.qwqer_address) {
                    shippingMethodTitle += " (" + extensionAttributesCheckoutConfig.qwqer_address + ")";
                }
            }

            return shippingMethodTitle;
        },
    };

    /**
     * Override default getShippingMethodTitle
     */
    return function (OriginShipping) {
        return OriginShipping.extend(mixin);
    };
});
