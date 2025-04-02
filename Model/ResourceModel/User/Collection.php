<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Model\ResourceModel\User;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
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
