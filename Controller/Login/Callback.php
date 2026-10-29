<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Controller\Login;

use Magento\Framework\App\State;

class Callback implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public function __construct(
        protected \Magento\Framework\App\Response\Http $response,
        protected \Magento\Framework\Controller\Result\Forward $forward,
        protected \Magento\Framework\Escaper $escaper,
        protected \Magento\Framework\UrlInterface $url,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Psr\Log\LoggerInterface $logger,
        protected \WikaGroup\AzureB2cSSO\Helper\Data $helper,
        protected State $appState,
    ) {
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
    {
        $redirect = $this->helper->getSession()->getSsoReferer() ?: $this->storeManager->getStore()->getBaseUrl();

        if ($this->helper->isLoggedIn()) {
            return $this->response->setRedirect($redirect);
        }

        if (!$this->helper->getSettings()->isSsoEnabled()) {
            return $this->forward->forward('noroute');
        }

        if (array_key_exists('error', $_GET)) {
            if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
                echo '<h1>Login with Azure B2C failed</h1>';
                echo '<strong>Error: </strong>' . $this->escaper->escapeHtml((string) $_GET['error']) . '<br>';
                if (array_key_exists('error_description', $_GET)) {
                    echo '<strong>Description: </strong>' . $this->escaper->escapeHtml((string) $_GET['error_description']) . '<br>';
                }
                if (array_key_exists('state', $_GET)) {
                    echo '<strong>State: </strong>' . $this->escaper->escapeHtml((string) $_GET['state']) . '<br>';
                }
                exit;
            } else {
                $this->logger->error('WikaGroup AzureB2cSSO: Login with Azure B2C failed', [
                    'error' => $_GET['error'] ?? '',
                    'error_description' => $_GET['error_description'] ?? '',
                    'state' => $_GET['state'] ?? '',
                ]);
                $this->helper->addUnspecifiedError();
                return $this->response->setRedirect($redirect);
            }
        }

        if (!array_key_exists('code', $_GET)) {
            $this->logger->error('WikaGroup AzureB2cSSO: Azure B2C did not return a code');
            $this->helper->addUnspecifiedError();
            return $this->response->setRedirect($redirect);
        }

        $user = $this->helper->newAzureB2cProvider()->getUserInfo($_GET['code']);
        $this->helper->loginAsUser($user);
        return $this->response->setRedirect($redirect);
    }
}
