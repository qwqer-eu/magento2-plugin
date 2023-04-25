<?php

namespace Qwqer\Express\Model\Api;

use Exception;
use Qwqer\Express\Model\Api\AbstractRequest;

class GeoCode extends AbstractRequest
{
    /**
     * GeoCode getResponse
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
     * ExecuteRequest
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function executeRequest(array $params = []): array
    {
        $response = parent::executeRequest($params);
        $items = [];
        if (!empty($response) && !empty($response['data']['coordinates'])) {
            $items['coordinates'] = $response['data']['coordinates'];
        }
        return $items;
    }

    /**
     * GetEndpointUri
     *
     * @param array $params
     * @return string
     */
    protected function getEndpointUri(array $params): string
    {
        return $this->configurationProvider->getGeoCode();
    }

    /**
     * GetBodyParams
     *
     * @param array $params
     * @return array
     */
    protected function getBodyParams(array $params = []): array
    {
        return array_merge(
            [
                'body' => json_encode($params)
            ],
            $this->additionalBodyParams
        );
    }
}
