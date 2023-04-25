define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            if(window.checkoutConfig.qwqer && window.checkoutConfig.qwqer.enabled == 0) {
                return originalAction();
            }

            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            shippingAddress['extension_attributes']['qwqer_address'] = '';

            if ($("#carrier_address").length > 0) {
                shippingAddress['extension_attributes']['qwqer_address'] = $("#carrier_address").val();
            }

            return originalAction();
        });
    };
});
