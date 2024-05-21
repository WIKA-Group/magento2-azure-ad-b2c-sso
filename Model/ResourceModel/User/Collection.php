<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Model\ResourceModel\User;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            \WikaGroup\AzureB2cSSO\Model\User::class,
            \WikaGroup\AzureB2cSSO\Model\ResourceModel\User::class
        );
    }
}
