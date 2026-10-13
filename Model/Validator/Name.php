<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Validator\Name as MagentoNameValidator;

/**
 * Extends the core name pattern to additionally allow parentheses.
 */
class Name extends MagentoNameValidator
{
    private const string PATTERN_NAME = '/^[\p{L}\p{M}\,\-\_\.\'’`&\s\d\(\)"\/]{1,255}$/u';

    public function __construct(
        protected \WikaGroup\AzureB2cSSO\Helper\Settings $settings,
    ) {}

    /**
     * @param Customer $customer
     */
    public function isValid($customer): bool
    {
        if (!$this->settings->shouldUseExtendedValidationRules()) {
            return parent::isValid($customer);
        }

        if (!$this->isValidName($customer->getFirstname())) {
            $this->_addMessages([['firstname' => 'First Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getLastname())) {
            $this->_addMessages([['lastname' => 'Last Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getMiddlename())) {
            $this->_addMessages([['middlename' => 'Middle Name is not valid!']]);
        }

        return count($this->_messages) === 0;
    }

    private function isValidName(?string $nameValue): bool
    {
        if ($nameValue === null || $nameValue === '') {
            return true;
        }

        return preg_match(self::PATTERN_NAME, $nameValue) === 1;
    }
}
