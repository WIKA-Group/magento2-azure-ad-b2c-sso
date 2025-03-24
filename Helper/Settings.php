<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Settings extends AbstractHelper
{
    public function getRedirectUri(): string
    {
        return $this->_urlBuilder->getUrl("azureb2c/login/callback");
    }

    public function isSsoEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/enable_sso', ScopeInterface::SCOPE_STORES);
    }

    public function showButton(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/show_button', ScopeInterface::SCOPE_STORES);
    }

    public function createMagentoCustomer(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/create_mage_customer', ScopeInterface::SCOPE_STORES);
    }

    public function useCustomCss(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/use_custom_css', ScopeInterface::SCOPE_STORES);
    }

    public function getCustomContainerCss(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/general/custom_container_class', ScopeInterface::SCOPE_STORES);
    }

    public function getCustomButtonCss(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/general/custom_button_class', ScopeInterface::SCOPE_STORES);
    }

    public function logoutFromAzure(): bool
    {
        return (bool)$this->scopeConfig->getValue('azure_b2c/general/log_out_from_azure', ScopeInterface::SCOPE_STORES);
    }

    public function getClientId(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/client_id', ScopeInterface::SCOPE_STORES);
    }

    public function getClientSecret(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/client_secret', ScopeInterface::SCOPE_STORES);
    }

    public function getBaseUrl(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/base_url', ScopeInterface::SCOPE_STORES);
    }

    public function getDefaultAlgorithm(): string
    {
        return (string)$this->scopeConfig->getValue('azure_b2c/connection/default_algorithm', ScopeInterface::SCOPE_STORES);
    }

    public function getLeewayTime(): int
    {
        return (int)$this->scopeConfig->getValue('azure_b2c/connection/leeway', ScopeInterface::SCOPE_STORES);
    }
}