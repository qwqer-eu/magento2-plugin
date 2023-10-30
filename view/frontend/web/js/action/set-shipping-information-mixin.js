define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {

            if(window.checkoutConfig.qwqer
                && window.checkoutConfig.qwqer.enabled == 0
                && window.checkoutConfig.qwqer_door.enabled == 0
                && window.checkoutConfig.qwqer_parcel.enabled == 0
            ) {
                return originalAction();
            }

            var shippingAddress = quote.shippingAddress();

            //console.log('set-shipping-information-mixin.js', quote.shippingMethod(), shippingAddress);

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            shippingAddress['extension_attributes']['qwqer_address'] = '';

            if ($("#carrier_address_parcel").length > 0
                && quote.shippingMethod()['carrier_code'] == window.checkoutConfig.qwqer_parcel.methodCode) {
                console.log('carrier_address_parcel', $("#carrier_address_parcel").val(), quote.shippingMethod());
                shippingAddress['extension_attributes']['qwqer_address'] = $("#carrier_address_parcel").val();
            } else if ($("#carrier_address").length > 0) {
                console.log('carrier_address', $("#carrier_address").val(), quote.shippingMethod());
                shippingAddress['extension_attributes']['qwqer_address'] = $("#carrier_address").val();
            }

            return originalAction();
        });
    };
});
