<?php

namespace Qwqer\Express\Model\Api;

use Exception;
use Qwqer\Express\Model\Api\AbstractRequest;
use Qwqer\Express\Provider\ConfigurationProvider;

class GetOrdersList extends AbstractRequest
{
    /**
     * GetEndpointUri
     *
     * @param array $params
     * @return string
     */
    protected function getEndpointUri(array $params): string
    {
        return $this->configurationProvider->getOrdersList();
    }

    /**
     * GetBodyParams
     *
     * @param array $params
     * @return array
     */
    protected function getBodyParams(array $params = []): array
    {
        return $this->additionalBodyParams;
    }
}
