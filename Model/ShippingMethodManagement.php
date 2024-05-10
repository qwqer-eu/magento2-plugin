<?php

namespace Qwqer\Express\Model;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Qwqer\Express\Model\Api\GeoCode;
use Qwqer\Express\Model\Api\ShippingCost;
use Qwqer\Express\Model\Carrier\ScheduledToDoor;
use Qwqer\Express\Model\Carrier\ScheduledToParcel;
use Qwqer\Express\Model\Api\ParcelMachines;
use Qwqer\Express\Provider\ConfigurationProvider;

/**
 * Save QWQER address to customer attributes in quote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ShippingMethodManagement implements \Qwqer\Express\Api\ShipmentEstimationInterface
{
    /**
     * Quote repository model
     *
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $quoteRepository;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @var GeoCode
     */
    protected GeoCode $geoCode;

    /**
     * @var ShippingCost
     */
    protected ShippingCost $shippingCost;

    /**
     * @var ParcelMachines
     */
    private ParcelMachines $parcelMachines;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Request $request
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GeoCode $geoCode
     * @param ShippingCost $shippingCost
     * @param ParcelMachines $parcelMachines
     * @param ConfigurationProvider $ConfigurationProvider
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Request $request,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        GeoCode $geoCode,
        ShippingCost $shippingCost,
        ParcelMachines $parcelMachines,
        ConfigurationProvider $ConfigurationProvider
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->geoCode = $geoCode;
        $this->shippingCost = $shippingCost;
        $this->parcelMachines = $parcelMachines;
        $this->configurationProvider = $ConfigurationProvider;
    }

    /**
     * @param string $cartId
     * @return array|array[]
     * @throws NoSuchEntityException
     */
    public function estimateByExtendedAddress(string $cartId)
    {
        try {
            $quote = $this->quoteRepository->getActive($cartId);
        } catch (\Exception $e) {
            /** @var $quoteIdMask QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        }

        /** @var Quote $quote */
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        $params = $this->request->getBodyParams();

        $success = true;
        $message = '';
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $price = $this->configurationProvider->getStoreConfig(
            "carriers/".$this->configurationProvider->getShippingMethodCode($shippingMethod)."/base_shipping_cost"
        );

        if (isset($params['address']['data'])) {
            try {
                $addressString = $params['address']['data'];
                $params = ['address' => $addressString];

                $coordinates = [];
                if ($shippingMethod == ScheduledToParcel::METHOD_CODE) {
                    $response = $this->parcelMachines->getParcelDataByName($addressString);
                    if (!empty($response['coordinates'])) {
                        $coordinates['coordinates'] = $response['coordinates'];
                    }
                } else {
                    $coordinates = $this->geoCode->executeRequest($params);
                }

                if (!empty($coordinates)) {
                    $orderDataRequest = array_merge($params, $coordinates);

                    if ($shippingMethod == ScheduledToParcel::METHOD_CODE) {
                        $orderDataRequest['real_type'] = ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_PARCEL;
                        $orderDataRequest['parcel_size'] = $this->configurationProvider->getStoreConfig(
                            "carriers/".$this->configurationProvider->getShippingMethodCode($shippingMethod)."/parcel_size"
                        );
                    } elseif ($shippingMethod == ScheduledToDoor::METHOD_CODE) {
                        $orderDataRequest['real_type'] = ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_DOOR;
                    }
                    $result = $this->shippingCost->executeRequest($orderDataRequest);
                    if (!empty($result['data']) && isset($result['data']['client_price'])) {
                        $price = $result['data']['client_price'] / 100;
                        $quote->getShippingAddress()->getExtensionAttributes()->setQwqerAddress($addressString);
                    } else {
                        $success = false;
                        $message = __('Can not calculate shipping price');
                    }
                }

                $calculateShipping = $this->configurationProvider->getStoreConfigFlag(
                    "carriers/".$this->configurationProvider->getShippingMethodCode($shippingMethod)."/calculate_shipping_price"
                );
                if (!$calculateShipping) {
                    $price = $this->configurationProvider->getStoreConfig(
                    "carriers/".$this->configurationProvider->getShippingMethodCode($shippingMethod)."/base_shipping_cost"
                    );
                }

            } catch (\Exception $e) {
                $success = false;
                $message = $e->getMessage();
            }
        }
        $result = [
            [
                'price' => $price,
                'success' => $success,
                'message' => $message
            ],
        ];
        return $result;
    }
}
