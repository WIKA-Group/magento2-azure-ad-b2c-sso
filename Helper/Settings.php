<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Settings extends AbstractHelper
{
    public function getRedirectUri(): string
    {
        return $this->_urlBuilder->getUrl("azureb2c/login/callback");
    }

    public function isSsoEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/enable_sso');
    }

    public function showButton(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/show_button');
    }

    public function useCustomCss(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/use_custom_css');
    }

    public function logoutFromAzure(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/log_out_from_azure');
    }

    public function getClientId(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/client_id');
    }

    public function getClientSecret(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/client_secret');
    }

    public function getBaseUrl(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/base_url');
    }

    public function getDefaultAlgorithm(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/default_algorithm');
    }
}