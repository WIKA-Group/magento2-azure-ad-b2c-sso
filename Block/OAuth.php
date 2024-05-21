<?php

declare(strict_types=1);

namespace WikaGroup\AzureB2cSSO\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use WikaGroup\AzureB2cSSO\Helper\Data;

class OAuth extends Template
{
    public function __construct(
        protected Data $helper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getHelper(): Data
    {
        return $this->helper;
    }
}
