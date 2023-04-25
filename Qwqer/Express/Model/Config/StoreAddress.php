<?php

namespace Qwqer\Express\Model\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class StoreAddress extends AbstractStoreAddress
{
    /**
     * @var string
     */
    public $configValue = '';

    /**
     * GetAjaxUrl
     *
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('qwqer/address/autocomplete');
    }

    /**
     * GetConfigPrefix
     *
     * @return string
     */
    public function getConfigPrefix(): string
    {
        return "qwqer_store_address";
    }

    /**
     * GetMethodCode
     *
     * @return string
     */
    public function getMethodCode(): string
    {
        return \Qwqer\Express\Model\Carrier\Express::CARRIER_CODE;
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->configValue = $element->getEscapedValue();
        return $this->_toHtml();
    }

    /**
     * Return store address
     *
     * @return string
     */
    public function getConfigValue(): string
    {
        return $this->configValue;
    }
}
