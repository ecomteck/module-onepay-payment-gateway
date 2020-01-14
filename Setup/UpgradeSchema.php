<?php
/**
 * GiaPhuGroup Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GiaPhuGroup.com license that is
 * available through the world-wide-web at this URL:
 * https://www.giaphugroup.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CityDropdown
 * @copyright   Copyright (c) 2018-2019 GiaPhuGroup Co., Ltd. All rights reserved. (http://www.giaphugroup.com/)
 * @license     https://www.giaphugroup.com/LICENSE.txt
 */

namespace Ecomteck\OnePay\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $this->addOrderStatusOnePayPending($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add the order payment status OnePay pending
     *
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addOrderStatusOnePayPending(SchemaSetupInterface $setup)
    {
        /**
         * Install order statuses from config
         */
        $data = [];
        $statuses = [
            'payment_onepay_pending' => __('OnePay Pending')
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $setup->getConnection()->insertOnDuplicate($setup->getTable('sales_order_status'), $data);
    }
}
