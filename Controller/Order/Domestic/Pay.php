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

namespace Ecomteck\OnePay\Controller\Order\Domestic;

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
        $hasCode = $this->onePayHelperData->getDomesticCardHashCode();
        $responseParams = $this->getRequest()->getParams();
        ksort($responseParams);
        $md5HashData = '';
        foreach ($responseParams as $key => $value) {
            if ($key != 'vpc_SecureHash'
                && strlen($value) > 0
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
                        __('You paid by domestic ATM card via OnePay payment gateway successfully.')
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
            case '1':
                $result = __('Bank Declined Transaction.');
                break;
            case '3':
                $result = __('Merchant no longer exist.');
                break;
            case '4':
                $result = __('Invalid access code.');
                break;
            case '5':
                $result = __('Invalid amount.');
                break;
            case '6':
                $result = __('Invalid currency code.');
                break;
            case '7':
                $result = __('Unspecified Failure.');
                break;
            case '8':
                $result = __('Invalid card Number.');
                break;
            case '9':
            case '23':
                $result = __('Invalid card name.');
                break;
            case '10':
                $result = __('Expired Card.');
                break;
            case '11':
                $result = __('Card Not Registered Service Internet Banking.');
                break;
            case '12':
                $result = __('Invalid card date.');
                break;
            case '13':
                $result = __('Exist Amount.');
                break;
            case '21':
                $result = __('Insufficient fund.');
                break;
            case '24':
                $result = __('Invalid card info.');
                break;
            case '25':
                $result = __('Invalid OTP.');
                break;
            case '253':
                $result = __('Transaction timeout.');
                break;
            case '99':
                $result = __('User canceled transaction.');
                break;
            default:
                $result = __('Transaction was failed.');
        }
        return $result;
    }
}
