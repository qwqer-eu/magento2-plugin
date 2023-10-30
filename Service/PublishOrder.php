<?php

namespace Qwqer\Express\Service;

use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Qwqer\Express\Model\Api\GeoCode;
use Qwqer\Express\Model\Api\OrderPlace;
use Qwqer\Express\Model\Api\GetOrder;
use Qwqer\Express\Logger\Logger;
use Qwqer\Express\Model\Carrier\ScheduledToParcel;
use Qwqer\Express\Model\Carrier\ScheduledToDoor;
use Qwqer\Express\Provider\ConfigurationProvider;
use Magento\Framework\Exception\LocalizedException;
use Qwqer\Express\Model\Api\ParcelMachines;

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
     * @var ParcelMachines
     */
    private ParcelMachines $parcelMachines;

    /**
     * @param GeoCode $geoCode
     * @param OrderPlace $orderPlace
     * @param GetOrder $getOrder
     * @param ConfigurationProvider $configurationProvider
     * @param ParcelMachines $parcelMachines
     * @param Logger $logger
     */
    public function __construct(
        GeoCode $geoCode,
        OrderPlace $orderPlace,
        GetOrder $getOrder,
        ConfigurationProvider $configurationProvider,
        ParcelMachines $parcelMachines,
        Logger $logger
    ) {
        $this->geoCode = $geoCode;
        $this->orderPlace = $orderPlace;
        $this->getOrder = $getOrder;
        $this->configurationProvider = $configurationProvider;
        $this->parcelMachines = $parcelMachines;
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
        $phone = $order->getShippingAddress()->getTelephone()
            ? $order->getShippingAddress()->getTelephone() : $order->getBillingAddress()->getTelephone();

        $phone = str_replace(['(', ')', '-', ' ', '+'], ['', '', '', '', ''], $phone);
        $realType = ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE;

        if ($order->getShippingMethod() == ScheduledToDoor::METHOD_CODE) {
            $realType = ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_DOOR;
        } elseif ($order->getShippingMethod() == ScheduledToParcel::METHOD_CODE) {
            $realType = ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_PARCEL;
        }

        $originData = [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
            'phone' => "+".$phone,
            'address' => $quote->getShippingAddress()->getQwqerAddress(),
            'incrementId' => $order->getIncrementId()
        ];

        $coordinates = $this->geoCode->executeRequest($originData);
        if ($order->getShippingMethod() == ScheduledToParcel::METHOD_CODE) {
            $originData['parcel_size'] = $this->configurationProvider->getParcelSize();
            $response = $this->parcelMachines->getParcelDataByName($quote->getShippingAddress()->getQwqerAddress());
            if (!empty($response['coordinates'])) {
                $coordinates['coordinates'] = $response['coordinates'];
            }
        } else {
            $coordinates = $this->geoCode->executeRequest($originData);
        }

        $orderDataRequest = array_merge($originData, $coordinates);
        $orderDataRequest['real_type'] = $realType;

        return $this->orderPlace->executeRequest($orderDataRequest);
    }
}
