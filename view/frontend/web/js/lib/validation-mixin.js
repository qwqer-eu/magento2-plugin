define(
    ['jquery'],
    function($) {
        "use strict";
        return function(validator) {
            validator.addRule(
                'phone-validation-rule',
                function(value, element) {
                    return $.mage.isEmptyNoTrim(value) || !isNaN($.mage.parseNumber(value)) && /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im.test(value);
                },
                $.mage.__('Please enter valid phone number')
            );
            return validator;
        }
});
