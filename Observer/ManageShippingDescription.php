<?php

namespace Qwqer\Express\Observer;

use Magento\Framework\Event\ObserverInterface;
use Qwqer\Express\Logger\Logger;
use Qwqer\Express\Model\Carrier\Express;
use Qwqer\Express\Model\Carrier\ScheduledToDoor;
use Qwqer\Express\Model\Carrier\ScheduledToParcel;

class ManageShippingDescription implements ObserverInterface
{

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * ManageShippingDescription execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $shippingMethod = $order->getShippingMethod(true);
        $description = $order->getShippingDescription();

        if ($shippingMethod
            && ($shippingMethod->getData('carrier_code') == Express::CARRIER_CODE
                || $shippingMethod->getData('carrier_code') == ScheduledToDoor::CARRIER_CODE
                || $shippingMethod->getData('carrier_code') == ScheduledToParcel::CARRIER_CODE
            )
            && $quote->getShippingAddress()->getQwqerAddress()
        ) {
            $description .= " (". $quote->getShippingAddress()->getQwqerAddress() . " )";
            $order->setData('shipping_description', $description);
        }
        return $this;
    }
}
