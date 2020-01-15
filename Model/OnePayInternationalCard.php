<?php
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

namespace Ecomteck\OnePay\Model;

/**
 * Class OnePayInternationalCard
 */
class OnePayInternationalCard extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_ONEPAY_INTERNATIONAL_CARD_CODE = 'onepay_international';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_ONEPAY_INTERNATIONAL_CARD_CODE;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
}
