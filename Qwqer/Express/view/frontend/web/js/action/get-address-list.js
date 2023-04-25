define([
    "jquery",
    "mage/url"
], function (
    $,
    urlBuilder
) {
    "use strict";

    var currentRequest = null;

    var action = function (data, element = '') {
        currentRequest = $.ajax({
            url: urlBuilder.build("qwqer/api/address"),
            cache: false,
            data: {address: data},
            contentType: "application/json",
            type: "GET",
            dataType: 'json',
            beforeSend: function() {
                if(currentRequest != null) {
                    currentRequest.abort();
                }
            }
        });
        return currentRequest;
    };

    return action;
});
