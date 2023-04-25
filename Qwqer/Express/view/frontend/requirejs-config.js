var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Qwqer_Express/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Qwqer_Express/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Qwqer_Express/js/view/shipping-information-mixin': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Qwqer_Express/js/lib/validation-mixin': true
            },
            'mage/menu': {
                'Qwqer_Express/js/lib/mage/menu-mixin': true
            },
        }
    }
};
