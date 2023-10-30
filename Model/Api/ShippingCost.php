<?php

namespace Qwqer\Express\Model\Api;

use Exception;
use Qwqer\Express\Model\Api\AbstractRequest;
use Qwqer\Express\Provider\ConfigurationProvider;

class ShippingCost extends AbstractRequest
{
    /**
     * ShippingCost getResponse
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
        return $this->configurationProvider->getShippingCost($params);
    }

    /**
     * GetBodyParams
     *
     * @param array $params
     * @return array
     */
    protected function getBodyParams(array $params = []): array
    {
        $storeOwnerAddress = $params;
        $storeOwnerAddress["address"] = $this->configurationProvider->getStoreAddress();
        $storeOwnerAddress["coordinates"] = $this->configurationProvider->getStoreAddressLocation();

        $bodyArray =  [
            'type' => ConfigurationProvider::DELIVERY_ORDER_TYPES,
            'category' => $this->configurationProvider->getCategory(),
            'origin' => $storeOwnerAddress,
            'destinations' => [$params],
        ];

        if (!empty($params['real_type'])) {
            $bodyArray['real_type'] = $params['real_type'];
            if ($params['real_type'] == ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE_PARCEL
                && $this->configurationProvider->getParcelSize())
            {
                $bodyArray['parcel_size'] = $this->configurationProvider->getParcelSize();
            }
            unset($params['real_type']);
        }

        return array_merge(
            [
                'body' => json_encode($bodyArray)
            ],
            $this->additionalBodyParams
        );
    }
}
