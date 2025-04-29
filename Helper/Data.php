<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Helper;

use Magento\Customer\Model\Customer;
use WikaGroup\AzureB2cSSO\Model\AzureB2cProvider;
use WikaGroup\AzureB2cSSO\Model\User;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        private Settings $settings,
        private \Magento\Customer\Model\Session $session,
        private \Magento\Framework\Message\Manager $messageManager,
        private \Magento\Store\Model\StoreManagerInterface $storeManager,
        private \Magento\Framework\Event\ManagerInterface $eventManager,
        // Mage2 Customer
        private \Magento\Customer\Model\CustomerFactory $customerFactory,
        private \Magento\Customer\Model\ResourceModel\Customer $customerRes,
        private \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollFactory,
        // AzureB2C User
        private \WikaGroup\AzureB2cSSO\Model\UserFactory $userFactory,
        private \WikaGroup\AzureB2cSSO\Model\ResourceModel\User $userRes,
        private \WikaGroup\AzureB2cSSO\Model\ResourceModel\User\CollectionFactory $userCollFactory,
    ) {
        parent::__construct($context);
    }

    public function newAzureB2cProvider(): AzureB2cProvider
    {
        return new AzureB2cProvider($this->scopeConfig, $this->settings);
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function getSession(): \Magento\Customer\Model\Session
    {
        return $this->session;
    }

    public function isLoggedIn(): bool
    {
        return $this->session->isLoggedIn();
    }

    public function loginAsUser(array $userData): void
    {
        $isNewUser = false;

        // Search for OAuth ID first
        $customer = $this->findCustomerByOauthId($userData['oauthId']);

        // Search for email afterwards
        if ($customer == null) {
            $isNewUser = true;
            $customer = $this->findCustomerByEmail($userData['email']);
        }

        if ($customer == null || empty($customer->getId())) {
            // Create a new Magento customer
            if ($this->settings->createMagentoCustomer()) {
                $customer = $this->createCustomer($userData);
                if ($customer === null) {
                    return;
                }
            } else {
                $this->addError((string)__('There is no registered customer with the given email address.'));
                return;
            }
        }

        if ($isNewUser && !$this->createOauthUser($customer, $userData)) {
            return;
        }

        $this->updateCustomer($customer, $userData);

        if (!$this->session->loginById($customer->getId())) {
            $this->addUnspecifiedError();
        }
    }

    private function createOauthUser(Customer $customer, array $userData): bool
    {
        try {
            /** @var User $user */
            $user = $this->userFactory->create();
            $user->setCustomerId((int)$customer->getId());
            $user->setOauthUserId($userData['oauthId']);
            $this->userRes->save($user);

            return true;
        } catch (\Throwable $e) {
            $this->_logger->error('WikaGroup AzureB2cSSO: Failed to update OAuth user', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->addUnspecifiedError();

            return false;
        }
    }

    private function createCustomer(array $userData): ?Customer
    {
        try {
            /** @var Customer $customer */
            $customer = $this->customerFactory->create();
            if ($this->settings->shouldIgnoreValidation()) {
                $customer->setData('ignore_validation_flag', true);
            }

            $store = $this->storeManager->getStore();
            $customer->setWebsiteId($store->getWebsiteId());
            $customer->setGroupId($this->settings->getGroupId());
            $customer->setStoreId($store->getId());

            $customer->setEmail($userData['email']);
            $customer->setFirstname($userData['given_name'] ?? $userData['name'] ?? '');
            $customer->setLastname($userData['family_name'] ?? $userData['name'] ?? '');

            $this->customerRes->save($customer);

            $this->eventManager->dispatch('azure_b2c_sso_create_customer_after', ['user_data' => $userData]);

            return $customer;
        } catch (\Throwable $e) {
            $this->_logger->error('WikaGroup AzureB2cSSO: Failed to create customer', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->addUnspecifiedError();
            return null;
        }
    }

    private function updateCustomer(Customer $customer, array $userData): void
    {
        try {
            if ($this->settings->shouldIgnoreValidation()) {
                $customer->setData('ignore_validation_flag', true);
            }

            $customer->setEmail($userData['email']);
            $customer->setFirstname($userData['given_name'] ?? $userData['name'] ?? '');
            $customer->setLastname($userData['family_name'] ?? $userData['name'] ?? '');
            $this->customerRes->save($customer);

            $this->eventManager->dispatch('azure_b2c_sso_update_customer_after', ['user_data' => $userData]);
        } catch (\Throwable $e) {
            $this->_logger->error('WikaGroup AzureB2cSSO: Failed to update customer', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->addUnspecifiedError();
            return;
        }
    }

    public function addUnspecifiedError(): void
    {
        $this->addError(__('Customer Login') . ': ' . __('An unspecified error occurred. Please contact us for assistance.'));
    }

    public function addError(string $message): void
    {
        $this->messageManager->addError($message);
    }

    public function findCustomerByOauthId(string $oauthId): ?Customer
    {
        $userColl = $this->userCollFactory->create()->addFieldToFilter('oauth_user_id', $oauthId);
        if ($userColl->count() !== 1) {
            return null;
        }

        /** @var User $user */
        $user = $userColl->getFirstItem();
        if ($user->getId() === null) {
            return null;
        }

        $customer = $this->customerFactory->create();
        $this->customerRes->load($customer, $user->getCustomerId());
        return $customer;
    }

    public function findCustomerByEmail(string $email): ?Customer
    {
      $store = $this->storeManager->getStore();
      $customer = $this->customerFactory->create()->setWebsiteId($store->getWebsiteId())->loadByEmail($email);
      if ($customer == null)
      {
          return null;
      }
      return $customer;
    }
}
