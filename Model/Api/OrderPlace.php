<?php

namespace Qwqer\Express\Model\Api;

use Qwqer\Express\Provider\ConfigurationProvider;

class OrderPlace extends AbstractRequest
{
    /**
     * GetResponse
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getResponse(array $params = []): array
    {
        try {
            return $this->executeRequest->execute(
                $this->getEndpointUri($params),
                $this->getBodyParams($params),
                "POST"
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw $e;
        }
    }

    /**
     * GetEndpointUri
     *
     * @param array $params
     * @return string
     */
    protected function getEndpointUri(array $params): string
    {
        return $this->configurationProvider->getOrderPlaceUrl($params);
    }

    /**
     * GetBodyParams
     *
     * @param array $params
     * @return array
     */
    protected function getBodyParams(array $params = []): array
    {
        $orderId = $params['incrementId'];
        unset($params['incrementId']);
        $realType = $params['real_type'];
        unset($params['real_type']);
        $storeOwnerAddress = $params;
        $storeOwnerAddress["address"] = $this->configurationProvider->getStoreAddress();
        $storeOwnerAddress["coordinates"] = $this->configurationProvider->getStoreAddressLocation();

        $storeName = $this->configurationProvider->getStoreConfig('general/store_information/name');
        if(!empty($storeName)) {
            $storeOwnerAddress['name'] = $storeName;
        }
        $storeEmail = $this->configurationProvider->getStoreConfig('trans_email/ident_general/email');
        if(!empty($storeEmail)) {
            $storeOwnerAddress['email'] = $storeEmail;
        }
        $storePhone = $this->configurationProvider->getStoreConfig('general/store_information/phone');
        if(!empty($storePhone)) {
            $storeOwnerAddress['phone'] = $storePhone;
        }

        $bodyArray = [
            'type' => ConfigurationProvider::DELIVERY_ORDER_TYPES,
            'real_type' => $realType,
            'category' => $this->configurationProvider->getCategory(),
            'delivery_order_id' => $orderId,
            'origin' => $storeOwnerAddress,
            'destinations' => [$params],
        ];

        if ($realType == ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_PARCEL
            && $this->configurationProvider->getParcelSize())
        {
            $bodyArray['parcel_size'] = $this->configurationProvider->getParcelSize();
        }

        return array_merge(
            [
                'body' => json_encode($bodyArray)
            ],
            $this->additionalBodyParams
        );
    }
}
