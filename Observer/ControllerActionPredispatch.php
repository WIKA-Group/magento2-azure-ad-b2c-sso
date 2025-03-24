<?php 
namespace WikaGroup\AzureB2cSSO\Observer;

use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
use WikaGroup\AzureB2cSSO\Helper\Data;

class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface 
{
    public function __construct(
        private UrlInterface $url,
        private Data $helper,
        private Http $http,
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if ($this->helper->isLoggedIn() || !$this->helper->getSettings()->isAutologinEnabled()) {
            return;
        }

        $name = $this->helper->getSettings()->getGetName();
        if (!array_key_exists($name, $_GET)) {
            return;
        }

        $value = $this->helper->getSettings()->getGetValue();
        if ($_GET[$name] !== $value) {
            return;
        }

        $loginUri = $this->url->getUrl('azureb2c/login/authorize');
        $this->http->setRedirect($loginUri);
        $this->http->sendResponse();
        die();
    }
}