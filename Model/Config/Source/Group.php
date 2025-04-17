<?php

namespace WikaGroup\AzureB2cSSO\Model\Config\Source;

class Group implements \Magento\Framework\Data\OptionSourceInterface
{
    private ?array $options = null;

    public function __construct(
        private \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollFactory
    ) {
    }

    public function toOptionArray(): array
    {
        if ($this->options === null) {
            /** @var \Magento\Customer\Model\ResourceModel\Group\Collection $groups */
            $groups = $this->groupCollFactory->create();
            $this->options = $groups->load()->toOptionArray();
        }
        return $this->options;
    }
}
