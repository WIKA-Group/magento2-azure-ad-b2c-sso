<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Controller\Login;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use WikaGroup\AzureB2cSSO\Helper\Data;

class Callback implements HttpGetActionInterface
{
    public function __construct(
        protected StoreManagerInterface $storeManager,
        protected Forward $forward,
        protected State $appState,
        protected Http $response,
        protected UrlInterface $url,
        protected Data $helper,
    ) {
    }

    public function execute(): ResultInterface|ResponseInterface
    {
        if ($this->helper->isLoggedIn()) {
            return $this->response->setRedirect($this->storeManager->getStore()->getBaseUrl());
        }

        if (!$this->helper->getSettings()->isSsoEnabled()) {
            return $this->forward->forward('noroute');
        }

        if (array_key_exists('error', $_GET)) {
            if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
                echo '<h1>Login with Azure B2C failed</h1>';
                echo '<strong>Error: </strong>' . $_GET['error'] . '<br>';
                if (array_key_exists('error_description', $_GET)) {
                    echo '<strong>Description: </strong>' . $_GET['error_description'] . '<br>';
                }
                if (array_key_exists('state', $_GET)) {
                    echo '<strong>State: </strong>' . $_GET['state'] . '<br>';
                }
                exit;
            } else {
                $this->helper->addUnspecifiedError();
                return $this->response->setRedirect($this->storeManager->getStore()->getBaseUrl());
            }
        }

        if (!array_key_exists('code', $_GET)) {
            $this->helper->addUnspecifiedError();
            return $this->response->setRedirect($this->storeManager->getStore()->getBaseUrl());
        }

        $user = $this->helper->newAzureB2cProvider()->getUserInfo($_GET['code']);
        $this->helper->loginAsUser($user);
        return $this->response->setRedirect($this->storeManager->getStore()->getBaseUrl());
    }
}