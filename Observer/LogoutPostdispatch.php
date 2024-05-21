<?php 
namespace WikaGroup\AzureB2cSSO\Observer;

use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
use WikaGroup\AzureB2cSSO\Helper\Data;

class LogoutPostdispatch implements \Magento\Framework\Event\ObserverInterface 
{
    public function __construct(
        private UrlInterface $url,
        private Data $helper,
        private Http $http,
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->helper->getSettings()->logoutFromAzure()) {
            return;
        }

        $loggedOutUri = $this->url->getUrl('customer/account/logoutSuccess');
        $azureLogoutUri = $this->helper->newAzureB2cProvider()->getLogoutUri($loggedOutUri);
        $this->http->setRedirect($azureLogoutUri);
        $this->http->sendResponse();
        die();
    }
}