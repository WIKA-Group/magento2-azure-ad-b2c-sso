<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Model;

class User extends \Magento\Framework\Model\AbstractModel
{
    private const CUSTOMER_ID = 'customer_id';
    private const OAUTH_USER_ID = 'oauth_user_id';

    public function _construct()
    {
        $this->_init(\WikaGroup\AzureB2cSSO\Model\ResourceModel\User::class);
    }

    public function getCustomerId(): int
    {
        return intval($this->getData(self::CUSTOMER_ID));
    }

    public function setCustomerId(int $customerId): self
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getOauthUserId(): string
    {
        return (string)$this->getData(self::OAUTH_USER_ID);
    }

    public function setOauthUserId(string $oauthid): self
    {
        return $this->setData(self::OAUTH_USER_ID, $oauthid);
    }
}
