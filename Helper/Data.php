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

namespace Ecomteck\OnePay\Helper;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ONEPAY_DOMESTIC_CARD_PAYMENT_URL
        = 'payment/onepay_domestic/payment_url';

    const ONEPAY_DOMESTIC_CARD_ACCESS_CODE
        = 'payment/onepay_domestic/access_code';

    const ONEPAY_DOMESTIC_CARD_MERCHANT_ID
        = 'payment/onepay_domestic/merchant_id';

    const ONEPAY_DOMESTIC_CARD_HASH_CODE
        = 'payment/onepay_domestic/hash_code';

    const ONEPAY_DOMESTIC_CARD_QUERYDR_URL
        = 'payment/onepay_domestic/querydr_url';

    const ONEPAY_DOMESTIC_CARD_QUERYDR_USER
        = 'payment/onepay_domestic/querydr_user';

    const ONEPAY_DOMESTIC_CARD_QUERYDR_PASSWORD
        = 'payment/onepay_domestic/querydr_password';

    const ONEPAY_DOMESTIC_CARD_ORDER_PREFIX
        = 'payment/onepay_domestic/order_prefix';

    const ONEPAY_INTERNATIONAL_CARD_PAYMENT_URL
        = 'payment/onepay_international/payment_url';

    const ONEPAY_INTERNATIONAL_CARD_ACCESS_CODE
        = 'payment/onepay_international/access_code';

    const ONEPAY_INTERNATIONAL_CARD_MERCHANT_ID
        = 'payment/onepay_international/merchant_id';

    const ONEPAY_INTERNATIONAL_CARD_HASH_CODE
        = 'payment/onepay_international/hash_code';

    const ONEPAY_INTERNATIONAL_CARD_QUERYDR_URL
        = 'payment/onepay_international/querydr_url';

    const ONEPAY_INTERNATIONAL_CARD_QUERYDR_USER
        = 'payment/onepay_international/querydr_user';

    const ONEPAY_INTERNATIONAL_CARD_QUERYDR_PASSWORD
        = 'payment/onepay_international/querydr_password';

    const ONEPAY_INTERNATIONAL_CARD_ORDER_PREFIX
        = 'payment/onepay_domestic/order_prefix';

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Retrieve the OnePay Domestic card payment URL
     *
     * @return string
     */
    public function getDomesticCardPaymentUrl()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_PAYMENT_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay Domestic card access code
     *
     * @return string
     */
    public function getDomesticCardAccessCode()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_ACCESS_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay Domestic card merchant id
     *
     * @return string
     */
    public function getDomesticCardMerchantId()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_MERCHANT_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay Domestic card hash code
     *
     * @return string
     */
    public function getDomesticCardHashCode()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_HASH_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay Domestic card QueryDR URL
     *
     * @return string
     */
    public function getDomesticCardQueryDrUrl()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_QUERYDR_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay Domestic card QueryDR user
     *
     * @return string
     */
    public function getDomesticCardQueryDrUser()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_QUERYDR_USER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay Domestic card QueryDR password
     *
     * @return string
     */
    public function getDomesticCardQueryDrPassword()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_QUERYDR_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay Domestic card payment URL
     *
     * @return string
     */
    public function getDomesticCardOrderPrefix()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_DOMESTIC_CARD_ORDER_PREFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card payment URL
     *
     * @return string
     */
    public function getInternationalCardPaymentUrl()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_PAYMENT_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card access code
     *
     * @return string
     */
    public function getInternationalCardAccessCode()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_ACCESS_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card merchant id
     *
     * @return string
     */
    public function getInternationalCardMerchantId()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_MERCHANT_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card hash code
     *
     * @return string
     */
    public function getInternationalCardHashCode()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_HASH_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card QueryDR URL
     *
     * @return string
     */
    public function getInternationalCardQueryDrUrl()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_QUERYDR_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card QueryDR user
     *
     * @return string
     */
    public function getInternationalCardQueryDrUser()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_QUERYDR_USER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card QueryDR password
     *
     * @return string
     */
    public function getInternationalCardQueryDrPassword()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_QUERYDR_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the OnePay International card payment URL
     *
     * @return string
     */
    public function getInternationalCardOrderPrefix()
    {
        return $this->scopeConfig->getValue(
            self::ONEPAY_INTERNATIONAL_CARD_ORDER_PREFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the total paid
     *
     * @param  \Magento\Sales\Model\Order $orderObject
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTotalPaid($orderObject)
    {
        $baseCurrencyCode = $orderObject->getBaseCurrencyCode();
        switch ($baseCurrencyCode) {
            case 'VND':
                return $orderObject->getBaseGrandTotal();
            default:
                $orderCurrencyCode = $orderObject->getOrderCurrencyCode();
                if ($orderCurrencyCode == 'VND') {
                    return $orderObject->getGrandTotal();
                }

                $currencyRate = $this->storeManager->getStore()
                ->getBaseCurrency()
                ->getRate('VND');

                if ($currencyRate) {
                    return round($orderObject->getGrandTotal() * $currencyRate, 0);
                }
                return $orderObject->getGrandTotal();
        }
    }

    /**
     * Retrieve the base amount paid
     *
     * @param  \Magento\Sales\Model\Order $orderObject
     * @param  string                     $amount
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseAmountPaid($orderObject, $amount)
    {
        $baseCurrencyCode = $orderObject->getBaseCurrencyCode();
        switch ($baseCurrencyCode) {
            case 'VND':
                return $amount;
            default:
                $currencyRate = $this->storeManager->getStore()
                ->getBaseCurrency()
                ->getRate('VND');

                if ($currencyRate) {
                    return round($amount/$currencyRate, 0);
                }
                return $amount;
        }
    }

    /**
     * Retrieve the locale
     *
     * @return string
     */
    public function getLocale()
    {
        $locale = $this->localeResolver->getLocale();
        if ($locale == 'vi_VN') {
            return 'vn';
        }
        return 'en';
    }

    /**
     * Retrieve the amount paid by current store
     *
     * @param  \Magento\Sales\Model\Order $orderObject
     * @param  string                     $amount
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAmountPaid($orderObject, $amount)
    {
        $baseCurrencyCode = $orderObject->getBaseCurrencyCode();
        switch ($baseCurrencyCode) {
            case 'VND':
                $orderCurrencyCode = $orderObject->getOrderCurrencyCode();
                if ($orderCurrencyCode == 'VND') {
                    return $amount;
                }

                $currencyRate = $this->storeManager->getStore()
                ->getBaseCurrency()
                ->getRate($orderCurrencyCode);

                if ($currencyRate) {
                    return round($amount * $currencyRate, 0);
                }
                return $amount;
            default:
                $orderCurrencyCode = $orderObject->getOrderCurrencyCode();
                if ($orderCurrencyCode == 'VND') {
                    return $amount;
                }

                $currencyRate = $this->storeManager->getStore()
                ->getBaseCurrency()
                ->getRate('VND');

                if ($currencyRate) {
                    return round($amount / $currencyRate, 0);
                }
                return $amount;
        }
    }
}
