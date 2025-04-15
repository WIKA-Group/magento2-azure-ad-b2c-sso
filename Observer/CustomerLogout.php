<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Observer;

class CustomerLogout implements \Magento\Framework\Event\ObserverInterface 
{
    public function __construct(
        private \Magento\Framework\UrlInterface $url,
        private \WikaGroup\AzureB2cSSO\Helper\Data $helper,
        private \Magento\Framework\App\Response\Http $http,
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->helper->getSettings()->isSsoEnabled() ||
            !$this->helper->getSettings()->logoutFromAzure() ||
            php_sapi_name() === "cli"
        ) {
            return;
        }

        $loggedOutUri = $this->url->getUrl('customer/account/logoutSuccess');
        $azureLogoutUri = $this->helper->newAzureB2cProvider()->getLogoutUri($loggedOutUri);
        $this->http->setRedirect($azureLogoutUri);
        $this->http->sendResponse();
        die();
    }
}