<?php

namespace Qwqer\Express\Plugin\Quote;

use Qwqer\Express\Provider\ConfigurationProvider;

class AddressPlugin
{

    /**
     * @var ConfigurationProvider
     */
    protected ConfigurationProvider $configurationProvider;

    /**
     * @param ConfigurationProvider $configurationProvider
     */
    public function __construct(
        ConfigurationProvider $configurationProvider
    ) {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * BeforeSaveAddressInformation
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return void
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if ($this->configurationProvider->getIsQwqerEnabled()
            || $this->configurationProvider->getIsQwqerDoorEnabled()
            || $this->configurationProvider->getIsQwqerParcelEnabled()
        ) {
            $shippingAddress = $addressInformation->getShippingAddress();
            $ext = $shippingAddress->getExtensionAttributes();
            $shippingAddress->setQwqerAddress($ext->getQwqerAddress());
        }
    }
}
