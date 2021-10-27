define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'maxpay',
            component: 'Maxpay_Payment/js/view/payment/method-renderer/maxpay-method'
        }
    );

    return Component.extend({});
});
