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

        $options = [];
        if (array_key_exists('login_hint', $_GET)) {
            $options['login_hint'] = $_GET['login_hint'];
        } elseif ($this->request->getParam('login_hint') !== null) {
            $options['login_hint'] = $this->request->getParam('login_hint');
        }

        $this->helper->getSession()->setSsoReferer($this->redirect->getRefererUrl());
        $this->helper->newAzureB2cProvider()->authorize($options);
    }
}
