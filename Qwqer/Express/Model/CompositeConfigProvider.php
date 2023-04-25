<?php

namespace Qwqer\Express\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Qwqer\Express\Provider\ConfigurationProvider;

class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var array
     */
    protected $_validMessages;

    /**
     * @var ConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ConfigurationProvider $configurationProvider
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        ConfigurationProvider $configurationProvider
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * GetConfig
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $config = [];
        $quote = $this->_checkoutSession->getQuote();

        $config['qwqer']['enabled'] = (int) $this->configurationProvider->getIsQwqerEnabled();
        $config['qwqer']['methodCode'] = \Qwqer\Express\Model\Carrier\Express::CARRIER_CODE;
        if ($quote) {
            if (!empty($quote->getShippingAddress()->getQwqerAddress())) {
                $config['extension_attributes']['qwqer_address'] = $quote->getShippingAddress()->getQwqerAddress();
            }
        }

        return $config;
    }
}
