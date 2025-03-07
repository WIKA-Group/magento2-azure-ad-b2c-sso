<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Helper;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerRes;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Message\Manager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use WikaGroup\AzureB2cSSO\Model\AzureB2cProvider;
use WikaGroup\AzureB2cSSO\Model\ResourceModel\User as UserRes;
use WikaGroup\AzureB2cSSO\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use WikaGroup\AzureB2cSSO\Model\User;
use WikaGroup\AzureB2cSSO\Model\UserFactory;

class Data extends AbstractHelper
{
    public function __construct(
        Context $context,
        private Settings $settings,
        private Session $session,
        private LoggerInterface $logger,
        private Manager $messageManager,
        private StoreManagerInterface $storeManager,
        // Mage2 Customer
        private CustomerFactory $customerFactory,
        private CustomerRes $customerRes,
        private CollectionFactory $customerCollFactory,
        // AzureB2C User
        private UserFactory $userFactory,
        private UserRes $userRes,
        private UserCollectionFactory $userCollFactory,
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
        } catch (Throwable $e) {
            $this->logger->error('WikaGroup AzureB2cSSO: Failed to update OAuth user');
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
            $this->addUnspecifiedError();

            return false;
        }
    }

    private function createCustomer(array $userData): ?Customer
    {
        try {
            /** @var Customer $customer */
            $customer = $this->customerFactory->create();
            
            $store = $this->storeManager->getStore();
            $customer->setWebsiteId($store->getWebsiteId());
            $customer->setGroupId($store->getStoreGroupId());
            $customer->setStoreId($store->getId());

            $customer->setEmail($userData['email']);
            $customer->setFirstname($userData['given_name'] ?? '');
            $customer->setLastname($userData['family_name'] ?? $userData['name'] ?? '');

            $this->customerRes->save($customer);
            return $customer;
        } catch (Throwable $e) {
            $this->logger->error('WikaGroup AzureB2cSSO: Failed to create customer');
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
            $this->addUnspecifiedError();
            return null;
        }
    }

    private function updateCustomer(Customer $customer, array $userData): void
    {
        try {
            $customer->setEmail($userData['email']);
            $customer->setFirstname($userData['given_name'] ?? '');
            $customer->setLastname($userData['family_name'] ?? $userData['name'] ?? '');
            $this->customerRes->save($customer);
        } catch (Throwable $e) {
            $this->logger->error('WikaGroup AzureB2cSSO: Failed to update customer');
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
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
        $customerColl = $this->customerCollFactory->create()->addFieldToFilter('email', $email);
        if ($customerColl->count() !== 1) {
            return null;
        }
        return $customerColl->getFirstItem();
    }
}