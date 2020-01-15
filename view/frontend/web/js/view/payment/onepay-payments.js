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
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
    ], function (Component, rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'onepay_domestic',
                component: 'Ecomteck_OnePay/js/view/payment/method-renderer/onepay-domestic'
            },
            {
                type: 'onepay_international',
                component: 'Ecomteck_OnePay/js/view/payment/method-renderer/onepay-international'
            }
        );
        /**
     * Add view logic here if needed 
    */
        return Component.extend({});
    }
);
