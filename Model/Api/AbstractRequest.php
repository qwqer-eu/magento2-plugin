<?php

namespace Qwqer\Express\Model\Api;

use Qwqer\Express\Logger\Logger;
use Qwqer\Express\Service\ExecuteRequest;
use Qwqer\Express\Provider\ConfigurationProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

abstract class AbstractRequest
{
    /**
     * @var array
     */
    protected array $additionalBodyParams = [];

    /**
     * @var ExecuteRequest
     */
    protected ExecuteRequest $executeRequest;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var ConfigurationProvider
     */
    protected ConfigurationProvider $configurationProvider;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @param ExecuteRequest $executeRequest
     * @param Logger $logger
     * @param ConfigurationProvider $configurationProvider
     * @param RequestInterface $request
     */
    public function __construct(
        ExecuteRequest $executeRequest,
        Logger $logger,
        ConfigurationProvider $configurationProvider,
        RequestInterface $request
    ) {
        $this->executeRequest = $executeRequest;
        $this->logger = $logger;
        $this->configurationProvider = $configurationProvider;
        $this->request = $request;
    }

    /**
     * ExecuteRequest
     *
     * @param array $params
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeRequest(array $params = []): array
    {
        return $this->getResponse($params);
    }

    /**
     * GetResponse
     *
     * @param array $params
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getResponse(array $params = []): array
    {
        try {
            return $this->executeRequest->execute(
                $this->getEndpointUri($params),
                $this->getBodyParams($params)
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw $e;
        }
    }

    /**
     * Allow adding params.
     *
     * @param array $params
     * @return $this
     */
    public function addParams(array $params): self
    {
        $this->additionalBodyParams = array_merge($this->additionalBodyParams, $params);
        return $this;
    }

    /**
     * GetBodyParams
     *
     * @param array $params
     * @return array
     */
    abstract protected function getBodyParams(array $params = []): array;

    /**
     * GetEndpointUri
     *
     * @param array $params
     * @return string
     */
    abstract protected function getEndpointUri(array $params): string;
}
