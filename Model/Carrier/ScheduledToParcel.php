<?php

namespace Qwqer\Express\Model\Carrier;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Qwqer\Express\Model\Api\GeoCode;
use Qwqer\Express\Model\Api\ShippingCost;
use Qwqer\Express\Provider\ConfigurationProvider;
use Qwqer\Express\Model\Api\ParcelMachines;

class ScheduledToParcel extends AbstractCarrier implements CarrierInterface
{
    public const CARRIER_CODE = 'qwqer_parcel';

    public const METHOD_CODE = self::CARRIER_CODE."_".self::CARRIER_CODE;

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * @var bool
     */
    protected $_isFixed = false;

    /**
     * @var ResultFactory
     */
    private ResultFactory $rateResultFactory;

    /**
     * @var MethodFactory
     */
    private MethodFactory $rateMethodFactory;

    /**
     * @var GeoCode
     */
    protected GeoCode $geoCode;

    /**
     * @var ShippingCost
     */
    protected ShippingCost $shippingCost;

    /**
     * @var Session
     */
    private Session $_checkoutSession;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ConfigurationProvider
     */
    private ConfigurationProvider $configurationProvider;

    /**
     * @var ParcelMachines
     */
    private ParcelMachines $parcelMachines;

    /**
     * Constructor ScheduledToParcel
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param GeoCode $geoCode
     * @param ShippingCost $shippingCost
     * @param Session $_checkoutSession
     * @param ConfigurationProvider $configurationProvider
     * @param ParcelMachines $parcelMachines
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        GeoCode $geoCode,
        ShippingCost $shippingCost,
        Session $_checkoutSession,
        ConfigurationProvider $configurationProvider,
        ParcelMachines $parcelMachines,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->geoCode = $geoCode;
        $this->shippingCost = $shippingCost;
        $this->_checkoutSession = $_checkoutSession;
        $this->configurationProvider = $configurationProvider;
        $this->parcelMachines = $parcelMachines;
        $this->_logger = $logger;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return false|Result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $available = $this->checkAvailableProduct();
        if (!$available) {
            return false;
        }

        /** @var Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $shippingCost = $this->calculatePrice();

        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        /** @var Result $result */
        $result = $this->rateResultFactory->create();
        $result->append($method);

        return $result;
    }

    /**
     * @return false|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkAvailableProduct()
    {
        $items = $this->_checkoutSession->getQuote()->getItems();
        foreach ($items as $item) {
            $isAvailable = intval($item->getProduct()->getData(ConfigurationProvider::ATTRIBUTE_CODE_AVAILABILITY));
            if($isAvailable) {
                continue;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Calculate Price
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculatePrice(): float
    {
        $address = $this->_checkoutSession->getQuote()->getShippingAddress()->getData('qwqer_address');
        $price = $this->getConfigData('shipping_cost');
        $calculatePrice = $this->getConfigData('calculate_shipping_price');

        if ($address && $calculatePrice) {
            $params = ['address' => $address];
            try {
                $response = $this->parcelMachines->getParcelDataByName($address);
                if (!empty($response['coordinates'])) {
                    $coordinates['coordinates'] = $response['coordinates'];
                    $orderDataRequest = array_merge($params, $coordinates);
                    $orderDataRequest['real_type'] = ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_PARCEL;
                    $orderDataRequest['parcel_size'] = $this->getConfigData('parcel_size');
                    $result = $this->shippingCost->executeRequest($orderDataRequest);

                    if (!empty($result['data']) && isset($result['data']['client_price'])) {
                        $price = $result['data']['client_price'] / 100;
                    }
                }
            } catch (\Exception $exception) {
                $this->_logger->error($exception->getMessage());
            }
        } elseif (!$calculatePrice) {
            $price = $this->getConfigData('base_shipping_cost');
        }
        return (float) $price;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @inheritdoc
     */
    public function isTrackingAvailable()
    {
        return true;
    }
}
