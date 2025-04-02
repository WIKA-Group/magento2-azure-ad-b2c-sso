<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Observer;

class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface 
{
    public function __construct(
        private \Magento\Framework\UrlInterface $url,
        private \WikaGroup\AzureB2cSSO\Helper\Data $helper,
        private \Magento\Framework\App\Response\Http $http,
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if ($this->helper->isLoggedIn() ||
            !$this->helper->getSettings()->isSsoEnabled() ||
            !$this->helper->getSettings()->isAutologinEnabled()
        ) {
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