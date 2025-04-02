<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Controller\Login;

use Magento\Framework\App\State;

class Callback implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\Controller\Result\Forward $forward,
        protected State $appState,
        protected \Magento\Framework\App\Response\Http $response,
        protected \Magento\Framework\UrlInterface $url,
        protected \WikaGroup\AzureB2cSSO\Helper\Data $helper,
    ) {
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
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
        return $this->response->setRedirect($this->helper->getSession()->getSsoReferer());
    }
}