<?php

namespace Qwqer\Express\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigurationProvider
{
    /** GENERAL */
    public const API_IS_ENABLED = 'carriers/qwqer/active';
    public const API_BEARER_TOKEN = 'carriers/qwqer/api_bearer_token';
    public const API_BASE_URL_PATH = 'carriers/qwqer/auth_endpoint';
    public const API_TRANDING_POINT_ID = 'carriers/qwqer/trading_point_id';
    public const API_STORE_ADDRESS = 'carriers/qwqer/store_address';
    public const API_STORE_ADDRESS_LOCATION = 'carriers/qwqer/geo_store';
    public const API_CATEGORY = 'carriers/qwqer/category';
    public const API_AUTOCOMPLETE_URL = '/v1/places/autocomplete';
    public const API_GEOCODE_URL = '/v1/places/geocode';
    public const API_ORDER_PRICE_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders/get-price';
    public const API_ORDER_CREATE_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders';
    public const API_ORDER_LIST_URL
        = '/v1/plugins/magento/clients/auth/trading-points/{trading-point-id}/delivery-orders';
    public const API_ORDER_DETAILS_URL = '/v1/plugins/magento/delivery-orders/{order-id}';

    public const DELIVERY_ORDER_TYPES = "Regular";
    public const DELIVERY_ORDER_REAL_TYPE = "ScheduledDelivery";

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
     * Get is API base url
     *
     * @return string
     */
    public function getAPIBaseUrl(): string
    {
        return (string) $this->scopeConfig->getValue(
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
        return (string)  $this->scopeConfig->getValue(
            self::API_BEARER_TOKEN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get API API_TRANDING_POINT_ID
     *
     * @return string
     */
    public function getTrandingPointId(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::API_TRANDING_POINT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get API category
     *
     * @return string
     */
    public function getCategory(): string
    {
        return (string) $this->scopeConfig->getValue(
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
        return (string) $this->scopeConfig->getValue(
            self::API_STORE_ADDRESS,
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
        return str_replace('{trading-point-id}', $this->getTrandingPointId(), self::API_ORDER_PRICE_URL);
    }

    /**
     * GetOrderPlaceUrl
     *
     * @param array $params
     * @return string
     */
    public function getOrderPlaceUrl(array $params): string
    {
        return str_replace('{trading-point-id}', $this->getTrandingPointId(), self::API_ORDER_CREATE_URL);
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
        return str_replace('{trading-point-id}', $this->getTrandingPointId(), self::API_ORDER_LIST_URL);
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
