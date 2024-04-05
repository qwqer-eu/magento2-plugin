<?php

namespace Qwqer\Express\Model\Api;

use Exception;

class TradingPoint extends AbstractRequest
{
    /**
     * TradingPoint getResponse
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
                "GET"
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
        if (!empty($response['data']['working_hours'])) {
            $response = $response['data']['working_hours'];
        }
        return $response;
    }

    /**
     * GetEndpointUri
     *
     * @param array $params
     * @return string
     */
    protected function getEndpointUri(array $params): string
    {
        return $this->configurationProvider->getTradingPointUrl();
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
                'include' => 'working_hours,merchant'
            ],
            $this->additionalBodyParams
        );
    }
}
