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

namespace Ecomteck\OnePay\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Install order statuses from config
         */
        $data = [];
        $statuses = [
            'payment_onepay_failed' => __('OnePay Failed'),
            'payment_onepay_pending' => __('OnePay Pending')
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $setup->getConnection()->insertOnDuplicate($setup->getTable('sales_order_status'), $data);

        $setup->endSetup();
    }
}
