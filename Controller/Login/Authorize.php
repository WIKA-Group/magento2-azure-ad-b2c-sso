<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Controller\Login;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\Forward;
use Magento\Store\Model\StoreManagerInterface;
use WikaGroup\AzureB2cSSO\Helper\Data;

class Authorize implements HttpGetActionInterface
{
    public function __construct(
        protected StoreManagerInterface $storeManager,
        protected Http $response,
        protected Forward $forward,
        protected Data $helper,
    ) {
    }

    public function execute(): void
    {
        if ($this->helper->isLoggedIn()) {
            $this->response->setRedirect($this->storeManager->getStore()->getBaseUrl());
            return;
        }
        if (!$this->helper->getSettings()->isSsoEnabled()) {
            $this->forward->forward('noroute');
            return;
        }
        $this->helper->newAzureB2cProvider()->authorize();
    }
}
