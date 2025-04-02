<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Block;

use WikaGroup\AzureB2cSSO\Helper\Data;

class OAuth extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        protected Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getHelper(): Data
    {
        return $this->helper;
    }
}
