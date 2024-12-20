<?php

namespace Qwqer\Express\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as FrameworkDateTime;
use Qwqer\Express\Model\Carrier\ScheduledToParcel;
use Qwqer\Express\Model\Carrier\ScheduledToDoor;
use Qwqer\Express\Model\Carrier\Express;

class ConfigurationProvider
{
    /** GENERAL */
    public const API_IS_ENABLED = 'carriers/qwqer/active';
    public const API_IS_ENABLED_DOOR = 'carriers/qwqer_door/active';
    public const API_IS_ENABLED_PARCEL = 'carriers/qwqer_parcel/active';

    public const API_BEARER_TOKEN = 'carriers/qwqer/api_bearer_token';
    public const API_BASE_URL_PATH = 'carriers/qwqer/auth_endpoint';
    public const API_TRADING_POINT_ID = 'carriers/qwqer/trading_point_id';
    public const API_STORE_ADDRESS = 'carriers/qwqer/store_address';
    public const API_STORE_ADDRESS_LOCATION = 'carriers/qwqer/geo_store';
    public const API_CATEGORY = 'carriers/qwqer/category';
    public const API_PARCEL_SIZE = 'carriers/qwqer_parcel/parcel_size';

    public const API_WORKING_HOURS = 'carriers/qwqer/working_hours';

    public const API_AUTOCOMPLETE_URL = '/v1/plugins/magento/places/autocomplete';
    public const API_GEOCODE_URL = '/v1/plugins/magento/places/geocode';
    public const API_ORDER_PRICE_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders/get-price';
    public const API_ORDER_CREATE_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders';
    public const API_ORDER_LIST_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders';
    public const API_ORDER_DETAILS_URL = '/v1/plugins/magento/delivery-orders/{order-id}';
    public const API_PARCEL_MACHINES_URL = '/v1/plugins/magento/parcel-machines';

    public const DELIVERY_ORDER_TYPES = "Regular";
    public const DELIVERY_ORDER_REAL_TYPE = "ExpressDelivery";
    public const DELIVERY_ORDER_REAL_TYPE_DOOR = "ScheduledDelivery";
    public const DELIVERY_ORDER_REAL_TYPE_PARCEL = "OmnivaParcelTerminal";
    public const ATTRIBUTE_CODE_AVAILABILITY = 'is_qwqer_available';

    public const API_TRADING_POINT_INFO
        = '/v1/plugins/magento/trading-points/{trading-point-id}';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var FrameworkDateTime
     */
    private FrameworkDateTime $dateTime;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param FrameworkDateTime $dateTime
     * @param Json $json
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FrameworkDateTime    $dateTime,
        Json                 $json
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->_json = $json;
    }

    /**
     * Get is API integration enabled
     *
     * @return bool
     */
    public function getIsQwqerEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::API_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get is API integration enabled
     *
     * @return bool
     */
    public function getIsQwqerDoorEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::API_IS_ENABLED_DOOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get is API integration enabled
     *
     * @return bool
     */
    public function getIsQwqerParcelEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::API_IS_ENABLED_PARCEL,
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Get parcel size
     *
     * @return string
     */
    public function getParcelSize(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::API_PARCEL_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get is API base url
     *
     * @return string
     */
    public function getAPIBaseUrl(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::API_BASE_URL_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get API bearer token
     *
     * @return string
     */
    public function getApiBearerToken(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::API_BEARER_TOKEN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get API API_TRADING_POINT_ID
     *
     * @return string
     */
    public function getTradingPointId(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::API_TRADING_POINT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get API API_TRADING_POINT_ID
     *
     * @return string
     */
    public function getTradingPointUrl(): string
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_TRADING_POINT_INFO);
    }

    /**
     * Get API category
     *
     * @return string
     */
    public function getCategory(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::API_CATEGORY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Store Address
     *
     * @return string
     */
    public function getStoreAddress(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::API_STORE_ADDRESS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $path
     * @return string
     */
    public function getStoreConfig($path): string
    {
        return (string)$this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $path
     * @return bool
     */
    public function getStoreConfigFlag($path): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Store Address Location
     *
     * @return array
     */
    public function getStoreAddressLocation(): array
    {
        $configData = $this->scopeConfig->getValue(
            self::API_STORE_ADDRESS_LOCATION,
            ScopeInterface::SCOPE_STORE
        );
        if (!empty($configData)) {
            return explode(",", $configData);
        }
        return [];
    }

    /**
     * GetAutocompleteUrl
     *
     * @return string
     */
    public function getAutocompleteUrl(): string
    {
        return self::API_AUTOCOMPLETE_URL;
    }

    /**
     * Get Parcel Machines Url
     *
     * @return string
     */
    public function getParcelMachinesUrl(): string
    {
        return self::API_PARCEL_MACHINES_URL;
    }

    /**
     * GetGeoCode
     *
     * @return string
     */
    public function getGeoCode(): string
    {
        return self::API_GEOCODE_URL;
    }

    /**
     * GetShippingCost
     *
     * @param array $params
     * @return string
     */
    public function getShippingCost(array $params): string
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_ORDER_PRICE_URL);
    }

    /**
     * GetOrderPlaceUrl
     *
     * @param array $params
     * @return string
     */
    public function getOrderPlaceUrl(array $params): string
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_ORDER_CREATE_URL);
    }

    /**
     * GetOrderInfoUrl
     *
     * @param string $orderId
     * @return string
     */
    public function getOrderInfoUrl(string $orderId): string
    {
        return str_replace('{order-id}', $orderId, self::API_ORDER_DETAILS_URL);
    }

    /**
     * GetOrdersList
     *
     * @return string
     */
    public function getOrdersList(): string
    {
        return str_replace('{trading-point-id}', $this->getTradingPointId(), self::API_ORDER_LIST_URL);
    }

    /**
     * @return bool
     */
    public function checkWorkingHours(): bool
    {
        $workingHoursConfig = $this->getStoreConfig(self::API_WORKING_HOURS);
        if(!$workingHoursConfig) {
            return true;
        }
        $workingHoursArray = $this->_json->unserialize($workingHoursConfig);
        if (!is_array($workingHoursArray) || empty($workingHoursArray)) {
            return true;
        }
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $today = $this->dateTime->date('N') * 1;
        $dayOfWeek = $days[$today];
        $isOpen = false;
        foreach ($workingHoursArray as $workingHour) {
            if (isset($workingHour['day_of_week'])
                && $workingHour['day_of_week'] == $dayOfWeek
                && isset($workingHour['time_from'])
                && isset($workingHour['time_to'])
            ) {
                $dateTime = new \DateTime();
                $startTimeObj = $dateTime::createFromFormat('H:i', $workingHour['time_from']);
                $endTimeObj = $dateTime->createFromFormat('H:i', $workingHour['time_to']);
                $currentTime = $this->dateTime->gmtDate('H:i');
                $currentTimeObj = $dateTime->createFromFormat('H:i', $currentTime);
                if ($currentTimeObj >= $startTimeObj && $currentTimeObj <= $endTimeObj) {
                    $isOpen = true;
                    break;
                }
            }
        }
        return $isOpen;
    }

    /**
     * @param $shippingMethod
     * @return string
     */
    public function getShippingMethodCode($shippingMethod) :string
    {
        if ($shippingMethod == ScheduledToParcel::METHOD_CODE) {
            return ScheduledToParcel::CARRIER_CODE;
        } elseif ($shippingMethod == ScheduledToDoor::METHOD_CODE) {
            return ScheduledToDoor::CARRIER_CODE;
        } elseif ($shippingMethod == Express::METHOD_CODE) {
            return Express::CARRIER_CODE;
        }
        return '';
    }

    /**
     * UseSslVerify
     *
     * @return bool
     */
    public function useSslVerify(): bool
    {
        return false;
    }

    /**
     * GetApiPassword
     *
     * @return string
     */
    public function getApiPassword(): string
    {
        return '';
    }

    /**
     * GetApiUsername
     *
     * @return string
     */
    public function getApiUsername(): string
    {
        return '';
    }
}
