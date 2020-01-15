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
 * Class Pay
 */
class Pay extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory     $orderFactory
     * @param \Ecomteck\OnePay\Helper\Data          $onePayHelperData
     * @param \Magento\Checkout\Model\Session       $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ecomteck\OnePay\Helper\Data $onePayHelperData,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->onePayHelperData = $onePayHelperData;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * OnePay calls back for updating the order status
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $vpcTxnResponseCode = $this->getRequest()->getParam('vpc_TxnResponseCode', '');
        $responseHash = $this->getRequest()->getParam('vpc_SecureHash', '');
        $hasCode = $this->onePayHelperData->getInternationalCardHashCode();
        $responseParams = $this->getRequest()->getParams();
        ksort($responseParams);
        $md5HashData = '';
        foreach ($responseParams as $key => $value) {
            if ($key != 'vpc_SecureHash' && strlen($value) > 0
                && ((substr($key, 0, 4) == 'vpc_') || (substr($key, 0, 5) == 'user_'))
            ) {
                $md5HashData .= $key . '=' . $value . '&';
            }
        }
        $md5HashData = rtrim($md5HashData, '&');
        $hash = strtoupper(hash_hmac('SHA256', $md5HashData, pack('H*', $hasCode)));
        $incrementId = $this->getRequest()->getParam('vpc_OrderInfo', '000000000');
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()
            && $this->checkoutSession->getLastOrderId() == $order->getId()
            && $hash == strtoupper($responseHash)
        ) {
            try {
                if ($vpcTxnResponseCode == '0') {
                    $amount = $this->getRequest()->getParam('vpc_Amount', '0');
                    $amount = floatval($amount)/100;
                    $order = $order->setTotalPaid(
                        $this->onePayHelperData->getAmountPaid($order, $amount)
                    )->setBaseTotalPaid(
                        $this->onePayHelperData->getBaseAmountPaid($order, $amount)
                    );
                    $order = $order->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
                    $this->messageManager->addSuccess(
                        __('You paid by International Card via OnePay payment gateway successfully.')
                    );
                    $path = 'checkout/onepage/success';
                } else {
                    $order = $order->setStatus('payment_onepay_failed');
                    $this->messageManager->addError(
                        __(
                            'Pay by OnePay payment gateway failed, %1',
                            $this->getResponseDescription($vpcTxnResponseCode)
                        )
                    );
                    $path = 'checkout/onepage/failure';
                }
                $order->save();
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
            return $this->resultRedirectFactory->create()->setPath($path);
        } else {
            $this->messageManager->addError(__('Pay by OnePay payment gateway failed.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
        }
    }

    /**
     * Retrieve the response description
     *
     * @param  string $responseCode
     * @return string
     */
    private function getResponseDescription($responseCode)
    {
        switch ($responseCode) {
            case '?':
                $result = __('Transaction status is unknown.');
                break;
            case '1':
            case '9':
                $result = __('Issuer Bank declined the transaction. Please contact Issuer Bank.');
                break;
            case '2':
                $result = __('Bank Declined Transaction.');
                break;
            case '3':
                $result = __('Issuer Bank declined the transaction.');
                break;
            case '4':
                $result = __('Your card is expired.');
                break;
            case '5':
                $result = __('Your credit account is insufficient funds.');
                break;
            case '6':
                $result = __('Error from Issuer Bank.');
                break;
            case '7':
                $result = __('Error when processing transaction.');
                break;
            case '8':
                $result = __('Issuer Bank does not support E-commerce transaction.');
                break;
            case '99':
                $result = __('User canceled transaction.');
                break;
            case 'B':
                $result = __('Cannot authenticated by 3D-Secure Program. Please contact Issuer Bank.');
                break;
            case 'E':
                $result = __('Wrong CSC entered or Issuer Bank declined the transaction. Please contact Issuer Bank.');
                break;
            case 'F':
                $result = __('3D Secure Authentication Failed.');
                break;
            default:
                $result = __('Transaction was failed.');
        }
        return $result;
    }
}
