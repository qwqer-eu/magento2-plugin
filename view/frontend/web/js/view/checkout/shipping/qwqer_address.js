define([
    'Magento_Ui/js/form/element/abstract',
    'mage/url',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'jquery',
    'jquery/ui',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/shipping-rate-service',
    'Qwqer_Express/js/action/get-address-list',
    'Qwqer_Express/js/model/resource-url-manager',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'uiRegistry',
    'mage/storage',
    'mage/translate'
], function (Abstract, url, ko, Component, quote, customer, $, ui, setShippingInformationAction, shippingRateService, getAddressList, resourceUrlManager, rateRegistry, registry, storage) {
    'use strict';

    return Abstract.extend({
        selectedMethod: ko.observable(''),
        errorMessageAddress: ko.observable(''),
        citiesList: ko.observableArray([]),
        selectedQwqerAddress: ko.observable(''),
        cityInput: '',
        isSelected: ko.observable(false),
        carrierCodeConfig: window.checkoutConfig.qwqer.methodCode,
        carrierCodeConfigDoor: window.checkoutConfig.qwqer_door.methodCode,
        /**
         * @param $element
         */
        initialize: function ($element) {
            this._super();
            let self = this;

            if(
                (window.checkoutConfig.qwqer && window.checkoutConfig.qwqer.enabled == 0) &&
                (window.checkoutConfig.qwqer_door && window.checkoutConfig.qwqer_door.enabled == 0)
            ) {
                return true;
            }

            quote.shippingMethod.subscribe(function (value) {
                console.log('quote.shippingMethod.subscribe', value.method_code, self.selectedQwqerAddress());
                if(value && (value.method_code == self.carrierCodeConfig || value.method_code == self.carrierCodeConfigDoor)) {
                    self.selectedMethod(value.method_code);
                    if (self.selectedQwqerAddress()) {
                        self.updateShippingAddressData(self.selectedQwqerAddress());
                    }
                } else {
                    self.selectedMethod('');
                }

                return true;
            });

            quote.shippingAddress.subscribe(function (address) {
                console.log('quote.shippingAddress.subscribe', self.selectedQwqerAddress());
                if ( self.selectedQwqerAddress()
                    && quote.shippingMethod()
                    && (quote.shippingMethod()['carrier_code'] == window.checkoutConfig.qwqer.methodCode || quote.shippingMethod()['carrier_code'] == window.checkoutConfig.qwqer_door.methodCode)
                    && (typeof address.extension_attributes === "undefined" || typeof address.extension_attributes.qwqer_address === "undefined")
                ){
                    self.updateShippingAddressData(self.selectedQwqerAddress());
                }
            });

            self.value.subscribe(function (value) {
                if(self.value() == '' && (self.selectedMethod() == self.carrierCodeConfig || self.selectedMethod() == self.carrierCodeConfigDoor)) {
                    self.errorMessageAddress($.mage.__("Field is required"));
                } else {
                    self.errorMessageAddress('');
                }
            })

            self.selectedQwqerAddress.subscribe(function (newValue) {
                let qwqerSelected = false;
                console.log('self.selectedQwqerAddress.subscribe method', self.selectedMethod());
                if(self.selectedMethod() == self.carrierCodeConfig
                    || self.selectedMethod() == self.carrierCodeConfigDoor) {
                    qwqerSelected = true;
                }
                if (!qwqerSelected) {
                    return true;
                }
                console.log('self.selectedQwqerAddress.subscribe', newValue);
                let updateFlag = true;
                if(!customer.isLoggedIn()) {
                    let loginFormSelector = 'form[data-role=email-with-possible-login]';
                    $(loginFormSelector).validation();
                    let emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                    if (!emailValidationResult) {
                        $(loginFormSelector + ' input[name=username]').trigger('focus');
                        updateFlag = false;
                        return true;
                    }

                    let requiredAddressFields = ["street.0", "telephone", "postcode", "city", "firstname", "lastname"];
                    $.each(requiredAddressFields, function( index, field ) {
                        //console.log(_.isEmpty($("[name='shippingAddress."+field+"'] input").val()));
                        if(_.isEmpty($("[name='shippingAddress."+field+"'] input").val())) {
                            registry.get('checkout.errors').messageContainer.addErrorMessage({
                                'message': $.mage.__('Please add correct Shipping Address.')
                            });
                            updateFlag = false;
                            return true;
                        }
                    });
                }

                if(updateFlag) {
                    let serviceUrl, payload;
                    serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddressQwqer(quote);
                    payload = JSON.stringify({
                            address: {
                                'data': newValue
                            }
                        }
                    );
                    storage.post(
                        serviceUrl, payload, false
                    ).always(function (response) {
                        if (quote && quote.shippingAddress() && quote.shippingMethod()) {
                            let shippingAddress = quote.shippingAddress();
                            rateRegistry.set(shippingAddress.getKey(), null);
                            rateRegistry.set(shippingAddress.getCacheKey(), null);
                            quote.shippingAddress(shippingAddress);
                            if(response.length && response[0].success == false) {
                                self.selectedQwqerAddress('');
                                $(self.cityInput).val('');
                                newValue = '';
                                self.errorMessageAddress($.mage.__("QWQER Delivery option is not available"));
                            }
                        }
                    });
                }
                self.updateShippingAddressData(newValue);
            });
        },

        /**
         * @param value
         */
        updateShippingAddressData: function(value) {
            let self = this;
            console.log(quote.shippingMethod(), self.selectedMethod);
            if (quote && quote.shippingAddress() && quote.shippingMethod()) {
                let shippingAddress = quote.shippingAddress();

                if (shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }
                shippingAddress['extension_attributes']['qwqer_address'] = value;
                quote.shippingAddress(shippingAddress);
                setShippingInformationAction();
            }
        },

        /**
         * Method after reload page
         *
         * @param request
         * @param response
         */
        initAutocomplete: function (request, response) {
            let self = this;
            self.cityInput = request;
            let addressListArray = [];
            $(self.cityInput).autocomplete({
                minLength: 3,
                maxRows: 20,
                source: function (term, response) {
                    getAddressList($(self.cityInput).val(), $('#qwqer-delivery')).done(function (responseData) {
                        let options = JSON.parse(responseData);
                        self.citiesList(options ?? '');
                        response(options);
                        addressListArray = options;
                    }).always(function () {});
                },
                change: function (event, ui) {
                    let valid = false;
                    if (event.target.value != '' && addressListArray.length > 0) {
                        let writtenItem = new RegExp("^" + $.ui.autocomplete.escapeRegex(event.target.value.toLowerCase()) + "$", "i");
                        $.each( addressListArray, function( i, el ) {
                            if (el.label.toLowerCase().match(writtenItem)) {
                                valid = true;
                                return false;
                            }
                        });
                    }
                    if (!valid) {
                        self.selectedQwqerAddress('');
                        $(self.cityInput).val('');
                        self.errorMessageAddress($.mage.__("Please add correct QWQER Address"));
                    }
                },
                select: function (event, ui) {
                    event.preventDefault();
                    //console.log('select', ui.item.label);
                    $(self.cityInput).val(ui.item.label);
                    self.selectedQwqerAddress(ui.item.label);
                    self.isSelected(true);
                },
                focus: function (event, ui) {
                    event.preventDefault();
                    $(self.cityInput).val(ui.item.label);
                },
                close: function (event, ui) {
                    //console.log('close', self.selectedQwqerAddress());
                    if(self.selectedQwqerAddress() && !self.isSelected()){
                        self.isSelected(true);
                    }
                    if (!self.isSelected()) {
                        self.selectedQwqerAddress('');
                        $(self.cityInput).val('');
                        self.errorMessageAddress($.mage.__("Field is required"));
                    }
                }
            });
        }
    });
});
