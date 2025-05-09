<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Controller\Login;

class Authorize implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\App\Response\Http $response,
        protected \Magento\Framework\App\Response\RedirectInterface $redirect,
        protected \Magento\Framework\App\Request\Http $request,
        protected \Magento\Framework\Controller\Result\Forward $forward,
        protected \WikaGroup\AzureB2cSSO\Helper\Data $helper,
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

        // Check request for "login_hint"
        $options = [];
        if (array_key_exists('login_hint', $_GET)) {
            $options['login_hint'] = $_GET['login_hint'];
        } elseif ($this->request->getParam('login_hint') !== null) {
            $options['login_hint'] = $this->request->getParam('login_hint');
        }

        // If authorize is called by autologin, do not change the referer
        if ($this->request->getParam('from-autologin') !== '1') {
            // Check if a referer was passed
            if (array_key_exists('referer', $_GET)) {
                $referer = $_GET['referer'];
            } elseif ($this->request->getParam('referer') !== null) {
                $referer = $this->request->getParam('referer');
            } else {
                $referer = $this->redirect->getRefererUrl();
            }
            $this->helper->getSession()->setSsoReferer($referer);
        }

        // Forward to Azure B2C
        $this->helper->newAzureB2cProvider()->authorize($options);
    }
}
