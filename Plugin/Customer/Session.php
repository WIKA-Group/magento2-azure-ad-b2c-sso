<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Plugin\Customer;

use \Magento\Customer\Model\Session as MageSession;

class Session
{
    public function __construct(
        private \Magento\Framework\Event\ManagerInterface $eventManager,
    ) {
    }

    public function afterLogout(MageSession $subject, MageSession $result): MageSession
    {
        $this->eventManager->dispatch('customer_logout_after');
        return $result;
    }
}