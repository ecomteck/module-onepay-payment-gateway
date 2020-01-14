/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_OnePay
 * @copyright Copyright (c) 2020 Ecomteck (https://ecomteck.com/)
 * @license   https://ecomteck.com/LICENSE.txt
 */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url',
        'jquery'
    ],
    function (Component, additionalValidators, redirectOnSuccessAction, urlBuilder, $) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    template: 'Ecomteck_OnePay/payment/onepay-domestic',
                },

                /**
                 * Place order.
                 */
                placeOrder: function (data, event) {
                    var self = this;

                    if (event) {
                        event.preventDefault();
                    }

                    if (this.validate() && additionalValidators.validate()) {
                        this.isPlaceOrderActionAllowed(false);

                        this.getPlaceOrderDeferredObject()
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                            function (orderID) {
                                    $.ajax(
                                        {
                                            url: urlBuilder.build('onepay_payment_portal/order/domestic_placeOrder'),
                                            data: {'order_id': orderID},
                                            dataType: 'json',
                                            type: 'POST'
                                        }
                                    ).done(
                                        function (response) {
                                            if (!response.error) {
                                                window.location.replace(response.payment_url);
                                                self.redirectAfterPlaceOrder = false;
                                            } else {
                                                redirectOnSuccessAction.execute();
                                            }
                                        }
                                    ).fail(
                                        function (response) {
                                            console.log(response);
                                            redirectOnSuccessAction.execute();
                                        }
                                    );

                                    self.afterPlaceOrder();
                            }
                        );
                        return true;
                    }

                    return false;
                }
            }
        );
    }
);
