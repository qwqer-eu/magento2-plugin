<?php

namespace Qwqer\Express\Model\Carrier;

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

class ScheduledToDoor extends AbstractCarrier implements CarrierInterface
{
    public const CARRIER_CODE = 'qwqer_door';

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
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Constructor Express
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param GeoCode $geoCode
     * @param ShippingCost $shippingCost
     * @param \Magento\Checkout\Model\Session $_checkoutSession
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
        \Magento\Checkout\Model\Session $_checkoutSession,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->geoCode = $geoCode;
        $this->shippingCost = $shippingCost;
        $this->_checkoutSession = $_checkoutSession;
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
        if(!$available) {
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
        if ($address) {
            $params = ['address' => $address];
            try {
                $coordinates = $this->geoCode->executeRequest($params);
                if (!empty($coordinates)) {
                    $orderDataRequest = array_merge($params, $coordinates);
                    $orderDataRequest['real_type'] = ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_DOOR;
                    $result = $this->shippingCost->executeRequest($orderDataRequest);
                    if (!empty($result['data']) && isset($result['data']['client_price'])) {
                        $price = $result['data']['client_price'] / 100;
                    }
                }
            } catch (\Exception $exception) {
                $this->_logger->error($exception->getMessage());
            }
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
