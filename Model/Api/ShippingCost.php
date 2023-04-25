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
        $originData = [
            'address' => $params['address'],
            'coordinates' => $params['coordinates'],
        ];

        $bodyArray =  [
            'type' => ConfigurationProvider::DELIVERY_ORDER_TYPES,
            'real_type' => ConfigurationProvider::DELIVERY_ORDER_REAL_TYPE,
            'category' => $this->configurationProvider->getCategory(),
            'origin' => $originData,
            'destinations' => [$originData],
        ];

        return array_merge(
            [
                'body' => json_encode($bodyArray)
            ],
            $this->additionalBodyParams
        );
    }
}
