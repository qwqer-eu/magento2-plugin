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
    'Qwqer_Express/js/action/get-parcels-list',
    'Qwqer_Express/js/model/resource-url-manager',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'uiRegistry',
    'mage/storage',
    'mage/translate'
], function (Abstract, url, ko, Component, quote, customer, $, ui, setShippingInformationAction, shippingRateService, getParcelsList, resourceUrlManager, rateRegistry, registry, storage) {
    'use strict';

    return Abstract.extend({
        selectedMethod: ko.observable(''),
        errorMessageParcel: ko.observable(''),
        parcelsList: ko.observableArray([]),
        selectedParcel: ko.observable(''),
        parcelInput: '',
        isSelected: ko.observable(false),
        carrierCodeConfigParcel: window.checkoutConfig.qwqer_parcel.methodCode,
        /**
         * @param $element
         */
        initialize: function ($element) {
            this._super();
            let self = this;

            if(window.checkoutConfig.qwqer_parcel && window.checkoutConfig.qwqer_parcel.enabled == 0) {
                return true;
            }

            quote.shippingMethod.subscribe(function (value) {
                console.log('quote.shippingMethod.subscribe', value);
                if(value && value.method_code == self.carrierCodeConfigParcel) {
                    console.log('quote.shippingMethod.subscribe 1', value.method_code);
                    self.selectedMethod(value.method_code);
                } else {
                    self.selectedMethod('');
                }

                return true;
            });

            quote.shippingAddress.subscribe(function (address) {
                //console.log('quote.shippingAddress.subscribe', address, quote);
                if ( self.selectedParcel()
                    && quote.shippingMethod()
                    && (quote.shippingMethod()['carrier_code'] == window.checkoutConfig.qwqer_parcel.methodCode)
                    && (typeof address.extension_attributes === "undefined" || typeof address.extension_attributes.qwqer_address === "undefined")
                ){
                    self.updateShippingAddressData(self.selectedParcel());
                }
            });

            self.value.subscribe(function (value) {
                console.log('self.value.subscribe', self.value(), value);
                if(self.value() == '' && value) {
                    self.value(value);
                }
                if(self.value() == '' && self.selectedMethod() == self.carrierCodeConfigParcel) {
                    self.errorMessageParcel($.mage.__("Field is required"));
                } else {
                    self.errorMessageParcel('');
                }
            })

            self.selectedParcel.subscribe(function (newValue) {
                if(self.selectedMethod() != self.carrierCodeConfigParcel) {
                    return true;
                }
                console.log('self.selectedParcel.subscribe', newValue);
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
                            if(response.length && response[0] == 0) {
                                self.selectedParcel('');
                                $(self.parcelInput).val('');
                                newValue = '';
                                self.errorMessageParcel($.mage.__("QWQER Parcel Machines Delivery option not available"));
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
            self.parcelInput = request;
            let parcelsListArray = [];
            let options = '';
            getParcelsList('all', $('#qwqer-delivery-parcel')).done(function (responseData) {
                options = JSON.parse(responseData);
                self.parcelsList(options ?? '');
                parcelsListArray = options;
                $(self.parcelInput).autocomplete({
                    minLength:0,
                    scroll: true,
                    source: function (request, response) {
                        var matcher = new RegExp(request.term.toLowerCase());
                        response($.grep(options, function (item) {
                            return matcher.test(item.label.toLowerCase());
                        }));
                    },
                    change: function (event, ui) {
                        let valid = false;
                        console.log('change');
                        if (event.target.value != '' && parcelsListArray.length > 0) {
                            let writtenItem = new RegExp("^" + $.ui.autocomplete.escapeRegex(event.target.value.toLowerCase()) + "$", "i");
                            $.each( parcelsListArray, function( i, el ) {
                                if (el.label.toLowerCase().match(writtenItem)) {
                                    valid = true;
                                    return false;
                                }
                            });
                        }
                        if (!valid) {
                            self.selectedParcel('');
                            $(self.parcelInput).val('');
                            self.errorMessageParcel($.mage.__("Please add correct QWQER Parcel Machine"));
                        }
                    },
                    select: function (event, ui) {
                        event.preventDefault();
                        console.log('select', ui.item.label);
                        self.value(ui.item.label);
                        $(self.parcelInput).val(ui.item.label);
                        self.selectedParcel(ui.item.label);
                        self.isSelected(true);
                    },
                    focus: function (event, ui) {
                        console.log('focus');
                        event.preventDefault();
                        $(self.parcelInput).val(ui.item.label);
                    },
                    close: function (event, ui) {
                        console.log('close', self.isSelected(), self.selectedParcel());
                        if(self.selectedParcel() && !self.isSelected()){
                            self.isSelected(true);
                        }
                        if (!self.isSelected()) {
                            self.selectedParcel('');
                            $(self.parcelInput).val('');
                            self.errorMessageParcel($.mage.__("Field is required"));
                        }
                    }
                }).focus(function() {
                    $(this).autocomplete("search", $(this).val());
                });
            });
        }
    });
});
