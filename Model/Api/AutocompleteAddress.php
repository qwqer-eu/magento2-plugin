<?php

namespace Qwqer\Express\Model\Api;

use Exception;

class AutocompleteAddress extends AbstractRequest
{
    /**
     * AutocompleteAddress getResponse
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

        if (!empty($response['data'])) {
            if ( isset($response['data']['statusCode'])
                && !in_array($response['data']['statusCode'], [200, 201], true)
            ) {
                return $items;
            }
            foreach ($response['data'] as $key => $value) {
                $items[] = [
                    'label' => $value,
                    'value' => $key
                ];
            }
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
        return $this->configurationProvider->getAutocompleteUrl();
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
