<?php

namespace Qwqer\Express\Service;

use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Qwqer\Express\Model\Api\GeoCode;
use Qwqer\Express\Model\Api\OrderPlace;
use Qwqer\Express\Model\Api\GetOrder;
use Qwqer\Express\Logger\Logger;
use Qwqer\Express\Provider\ConfigurationProvider;
use Magento\Framework\Exception\LocalizedException;

class PublishOrder
{
    /**
     * @var GeoCode
     */
    protected GeoCode $geoCode;

    /**
     * @var OrderPlace
     */
    protected OrderPlace $orderPlace;

    /**
     * @var GetOrder
     */
    protected GetOrder $getOrder;

    /**
     * @var ConfigurationProvider
     */
    protected ConfigurationProvider $configurationProvider;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param GeoCode $geoCode
     * @param OrderPlace $orderPlace
     * @param GetOrder $getOrder
     * @param ConfigurationProvider $configurationProvider
     * @param Logger $logger
     */
    public function __construct(
        GeoCode $geoCode,
        OrderPlace $orderPlace,
        GetOrder $getOrder,
        ConfigurationProvider $configurationProvider,
        Logger $logger
    ) {
        $this->geoCode = $geoCode;
        $this->orderPlace = $orderPlace;
        $this->getOrder = $getOrder;
        $this->configurationProvider = $configurationProvider;
        $this->logger = $logger;
    }

    /**
     * PublishOrder execute
     *
     * @param Order $order
     * @param Quote $quote
     * @return $this|array
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute(Order $order, Quote $quote)
    {
        if (!$this->configurationProvider->getIsQwqerEnabled()) {
            return $this;
        }

        $phone = $order->getShippingAddress()->getTelephone()
            ? $order->getShippingAddress()->getTelephone() : $order->getBillingAddress()->getTelephone();

        $phone = str_replace(['(', ')', '-', ' ', '+'], ['', '', '', '', ''], $phone);

        $originData = [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
            'phone' => "+".$phone,
            'address' => $quote->getShippingAddress()->getQwqerAddress(),
            'incrementId' => $order->getIncrementId()
        ];

        $coordinates = $this->geoCode->executeRequest($originData);
        $orderDataRequest = array_merge($originData, $coordinates);
        return $this->orderPlace->executeRequest($orderDataRequest);
    }
}
