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

namespace Ecomteck\OnePay\Controller\Order\International;

/**
 * Class PlaceOrder
 */
class PlaceOrder extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Sales\Model\OrderFactory                $orderFactory
     * @param \Ecomteck\OnePay\Helper\Data                     $onePayHelperData
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ecomteck\OnePay\Helper\Data $onePayHelperData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->onePayHelperData = $onePayHelperData;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Place Order action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = [
            'error' => true,
            'message' => __('Order ID no longer exist.')
        ];
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()
            && $this->getRequest()->getMethod() == 'POST'
        ) {
            if ($paymentUrl = $this->onePayInternational()) {
                $data['error'] = false;
                $data['message'] = __('Retrieve the payment URL successfully.');
                $data['payment_url'] = $paymentUrl;
            }
        }

        return $result->setData($data);
    }

    /**
     * Redirect to OnePay International Card
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function onePayInternational()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $orderObject = $this->orderFactory->create()->load($orderId);
        $paymentUrl = $this->onePayHelperData->getInternationalCardPaymentUrl();
        $accessCode = $this->onePayHelperData->getInternationalCardAccessCode();
        $merchantId = $this->onePayHelperData->getInternationalCardMerchantId();
        $hasCode = $this->onePayHelperData->getInternationalCardHashCode();
        $orderPrefix = $this->onePayHelperData->getInternationalCardOrderPrefix();
        $orderPrefix = $orderPrefix?$orderPrefix:'ecomteck';
        if ($orderObject->getId()
            && $paymentUrl
            && $accessCode
            && $merchantId
            && $hasCode
        ) {
            $returnUrl = $this->_url->getUrl('onepay_payment_portal/order/international_pay');
            $md5HashData = '';
            $incrementId = $orderObject->getIncrementId();
            $locale = $this->onePayHelperData->getLocale();
            $paymentUrl .= '?';
            $params = [
                'vpc_Version' => '2',
                'vpc_Command' => 'pay',
                'vpc_AccessCode' => $accessCode,
                'vpc_Merchant' => $merchantId,
                'vpc_Locale' => $locale,
                'vpc_ReturnURL' => $returnUrl,
                'vpc_MerchTxnRef'=> $orderPrefix.$incrementId,
                'vpc_OrderInfo'=> $incrementId,
                'vpc_Amount' => round($this->onePayHelperData->getTotalPaid($orderObject)*100, 0),
                'vpc_TicketNo' => $orderObject->getRemoteIp(),
                'AgainLink' => $this->_url->getUrl('checkout'),
                'Title' => __('OnePAY Payment Gateway')
            ];

            ksort($params);

            foreach ($params as $key => $value) {
                $paymentUrl .= urlencode($key) . '=' . urlencode($value) . '&';
                if (strlen($value) > 0 && (substr($key, 0, 4) == 'vpc_' || substr($key, 0, 5) == 'user_')) {
                    $md5HashData .= $key . '=' . $value . '&';
                }
            }
            $md5HashData = rtrim($md5HashData, '&');

            $hash = strtoupper(hash_hmac('SHA256', $md5HashData, pack('H*', $hasCode)));
            $vpcURL = 'vpc_SecureHash=' . $hash;
            $paymentUrl .= $vpcURL;
            return $paymentUrl;
        }
        return null;
    }
}
