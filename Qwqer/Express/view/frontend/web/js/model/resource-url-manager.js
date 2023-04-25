define([
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'mageUtils'
], function (customer, urlBuilder, utils) {
        'use strict';

        return {
              /**
             * @param {Object} quote
             * @return {*}
             */
            getUrlForEstimationShippingMethodsForNewAddressQwqer: function (quote) {
                var params = this.getCheckoutMethod() == 'guest' ? //eslint-disable-line eqeqeq
                        {
                            quoteId: quote.getQuoteId()
                        } : {},
                    urls = {
                        'guest': '/guest-carts/:quoteId/qwqer-estimate-shipping-methods',
                        'customer': '/carts/mine/qwqer-estimate-shipping-methods'
                    };

                return this.getUrl(urls, params);
            },

            /**
             * @return {String}
             */
            getCheckoutMethod: function () {
                return customer.isLoggedIn() ? 'customer' : 'guest';
            },

            /**
             * Get url for service.
             *
             * @param {*} urls
             * @param {*} urlParams
             * @return {String|*}
             */
            getUrl: function (urls, urlParams) {
                var url;

                if (utils.isEmpty(urls)) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(urls['default'])) {
                    url = urls['default'];
                } else {
                    url = urls[this.getCheckoutMethod()];
                }

                return urlBuilder.createUrl(url, urlParams);
            },
        };
    }
);
