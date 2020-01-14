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

namespace Ecomteck\OnePay\Cron;

use \Magento\Sales\Model\Order;

/**
 * Class QueryDr
 */
class QueryDr
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Ecomteck\OnePay\Helper\Data
     */
    protected $onePayHelperData;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Sales\Model\OrderFactory                    $orderFactory
     * @param \Ecomteck\OnePay\Helper\Data                         $onePayHelperData
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Psr\Log\LoggerInterface                             $logger
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ecomteck\OnePay\Helper\Data $onePayHelperData,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->onePayHelperData = $onePayHelperData;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * Processing update order status
     *
     * @return bool
     */
    public function execute()
    {
        $currentDateTime = strtotime(date('Y-m-d H:i:s'));
        $sixteenMinutesBefore = $currentDateTime - 17*60;
        $dateTimeSixteenMinutesBefore = date('Y-m-d H:i:s', $sixteenMinutesBefore);
        $orderCollection = $this->orderFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                'status',
                'pending'
            )->addFieldToFilter(
                'created_at',
                ['lteq' => $dateTimeSixteenMinutesBefore]
            );
        $orderIdsFromOnePayDomestic = [];
        $orderIdsFromOnePayInternational = [];
        foreach ($orderCollection as $order) {
            $payment = $order->getPayment();
            if ($payment) {
                $paymentMethod = $payment->getMethod();
                if ($paymentMethod == 'onepay_domestic') {
                    $orderIdsFromOnePayDomestic[] = $order->getIncrementId();
                } elseif ($paymentMethod == 'onepay_international') {
                    $orderIdsFromOnePayInternational[] = $order->getIncrementId();
                }
            }
        }

        // Processing orders with the payment from OnePay Domestic
        if (!empty($orderIdsFromOnePayDomestic)) {
            $this->logger->critical('Starting update order status by QueryDR from OnePay');
            $merchantId = $this->onePayHelperData->getDomesticCardMerchantId();
            $accessCode = $this->onePayHelperData->getDomesticCardAccessCode();
            $queryDrUser = $this->onePayHelperData->getDomesticCardQueryDrUser();
            $queryDrPassword = $this->onePayHelperData->getDomesticCardQueryDrPassword();
            $orderPrefix = $this->onePayHelperData->getDomesticCardOrderPrefix();
            $orderPrefix = $orderPrefix?$orderPrefix:'ecomteck';
            foreach ($orderIdsFromOnePayDomestic as $orderId) {
                $targetUrl = $this->onePayHelperData->getDomesticCardQueryDrUrl();
                $postFields =
                    'vpc_Command=queryDR&vpc_Version=2&vpc_MerchTxnRef='.
                    $orderPrefix.
                    $orderId.
                    '&vpc_Merchant='.
                    $merchantId.
                    '&vpc_AccessCode='.
                    $accessCode.
                    '&vpc_User='.
                    $queryDrUser.
                    '&vpc_Password='.$queryDrPassword;

                try {
                    $this->logger->critical('Processing for Order Increment ID: '.$orderId);
                    ob_start();
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $targetUrl);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    if (empty($result) || !isset($result)) {
                        $this->logger->critical('The result is unknown due to connection problem');
                        ob_end_clean();
                        break;
                    }
                    $response = ob_get_contents();
                    ob_end_clean();
                    $this->logger->critical($response);
                    // search if $response contains HTML error code
                    if (strchr($response, '<html>')) {
                        $this->logger->critical('The result contains HTML error code');
                        break;
                    }
                    $responseCode = 'failed';
                    $orderInfo = '';
                    $params = explode('&', $response);
                    $map = [];
                    foreach ($params as $param) {
                        $explode = explode('=', $param);
                        if (count($explode) >= 2) {
                            $map[urldecode($explode[0])] = urldecode($explode[1]);
                        }
                    }
                    if (isset($map['vpc_TxnResponseCode'])) {
                        $responseCode = $map['vpc_TxnResponseCode'];
                    }
                    if (isset($map['vpc_OrderInfo'])) {
                        $orderInfo = $map['vpc_OrderInfo'];
                    }
                    if ($responseCode == '0' && $orderInfo == $orderId) {
                        $this->orderFactory->create()->loadByIncrementId($orderId)->setStatus(
                            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
                        )->save();
                        $this->logger
                            ->critical(
                                'Updated the status of order Increment ID: '.$orderId.' to '.Order::STATE_PAYMENT_REVIEW
                            );
                    } elseif ($responseCode == '300' && $orderInfo == $orderId) {
                        $this->orderFactory
                            ->create()
                            ->loadByIncrementId($orderId)
                            ->setStatus('payment_onepay_pending')
                            ->save();
                        $this->logger
                            ->critical('Updated the status of order Increment ID: '.$orderId.' to "OnePay Pending"');
                    } else {
                        $this->orderFactory
                            ->create()
                            ->loadByIncrementId($orderId)
                            ->setStatus('payment_onepay_failed')
                            ->save();
                        $this->logger
                            ->critical('Updated the status of order Increment ID: '.$orderId.' to "OnePay Failed"');
                        if ($orderInfo != $orderId) {
                            $message =
                                'Error could not find order Increment ID: '.$orderId.' on OnePay Payment Gateway';

                            $this->logger->critical($message);
                            $this->orderFactory
                                ->create()
                                ->loadByIncrementId($orderId)
                                ->addStatusHistoryComment($message)
                                ->setIsCustomerNotified(false)
                                ->setEntityName('order')
                                ->save();
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    return false;
                }
            }
            $this->logger->critical('The End.');
        }

        // Processing orders with the payment from OnePay International
        if (!empty($orderIdsFromOnePayInternational)) {
            $this->logger->critical('Starting update order status by QueryDR from OnePay');
            $merchantId = $this->onePayHelperData->getInternationalCardMerchantId();
            $accessCode = $this->onePayHelperData->getInternationalCardAccessCode();
            $queryDrUser = $this->onePayHelperData->getInternationalCardQueryDrUser();
            $queryDrPassword = $this->onePayHelperData->getInternationalCardQueryDrPassword();
            $orderPrefix = $this->onePayHelperData->getInternationalCardOrderPrefix();
            $orderPrefix = $orderPrefix?$orderPrefix:'ecomteck';
            foreach ($orderIdsFromOnePayInternational as $orderId) {
                $targetUrl = $this->onePayHelperData->getInternationalCardQueryDrUrl();
                $postFields =
                    'vpc_Command=queryDR&vpc_Version=2&vpc_MerchTxnRef='.
                    $orderPrefix.
                    $orderId.
                    '&vpc_Merchant='.
                    $merchantId.
                    '&vpc_AccessCode='.
                    $accessCode.
                    '&vpc_User='.
                    $queryDrUser.
                    '&vpc_Password='.
                    $queryDrPassword;

                try {
                    $this->logger->critical('Processing for Order Increment ID: '.$orderId);
                    ob_start();
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $targetUrl);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    if (empty($result) || !isset($result)) {
                        $this->logger->critical('The result is unknown due to connection problem');
                        ob_end_clean();
                        break;
                    }
                    $response = ob_get_contents();
                    ob_end_clean();
                    $this->logger->critical($response);
                    // search if $response contains HTML error code
                    if (strchr($response, '<html>')) {
                        $this->logger->critical('The result contains HTML error code');
                        break;
                    }
                    $responseCode = 'failed';
                    $orderInfo = '';
                    $params = explode('&', $response);
                    $map = [];
                    foreach ($params as $param) {
                        $explode = explode('=', $param);
                        if (count($explode) >= 2) {
                            $map[urldecode($explode[0])] = urldecode($explode[1]);
                        }
                    }
                    if (isset($map['vpc_TxnResponseCode'])) {
                        $responseCode = $map['vpc_TxnResponseCode'];
                    }
                    if (isset($map['vpc_OrderInfo'])) {
                        $orderInfo = $map['vpc_OrderInfo'];
                    }
                    if ($responseCode == '0' && $orderInfo == $orderId) {
                        $this->orderFactory->create()->loadByIncrementId($orderId)->setStatus(
                            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
                        )->save();
                        $this->logger
                            ->critical(
                                'Updated the status of order Increment ID: '.$orderId.' to '.Order::STATE_PAYMENT_REVIEW
                            );
                    } else {
                        $this->orderFactory
                            ->create()
                            ->loadByIncrementId($orderId)
                            ->setStatus('payment_onepay_failed')
                            ->save();
                        $this->logger
                            ->critical('Updated the status of order Increment ID: '.$orderId.' to "OnePay Failed"');
                        if ($orderInfo != $orderId) {
                            $message =
                                'Error could not find order Increment ID: '.$orderId.' on OnePay Payment Gateway';
                            $this->logger->critical($message);
                            $this->orderFactory
                                ->create()
                                ->loadByIncrementId($orderId)
                                ->addStatusHistoryComment($message)
                                ->setIsCustomerNotified(false)
                                ->setEntityName('order')
                                ->save();
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    return false;
                }
            }
            $this->logger->critical('The End.');
        }
    }
}
