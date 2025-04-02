<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Model\ResourceModel;

class User extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('wikagroup_azureb2c_user', 'entity_id');
    }
}