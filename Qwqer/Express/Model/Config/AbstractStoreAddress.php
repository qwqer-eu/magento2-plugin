<?php

namespace Qwqer\Express\Model\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
abstract class AbstractStoreAddress extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Qwqer_Express::input.phtml';

    /**
     * Return ajax url for send button
     *
     * @return string
     */
    abstract public function getAjaxUrl(): string;

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get Config Prefix
     *
     * @return string
     */
    abstract public function getConfigPrefix(): string;

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
        return $this->_toHtml();
    }
}
