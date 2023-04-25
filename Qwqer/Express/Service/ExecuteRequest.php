<?php

namespace Qwqer\Express\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\ClientFactory;
use Qwqer\Express\Logger\Logger;
use Qwqer\Express\Provider\ConfigurationProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Rest\Request;

class ExecuteRequest
{
    public const FORBIDDEN_HTTP_CODE = 403;

    /**
     * @var ClientFactory
     */
    protected ClientFactory $clientFactory;

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @var ConfigurationProvider
     */
    protected ConfigurationProvider $configurationProvider;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param ClientFactory $clientFactory
     * @param Json $json
     * @param ConfigurationProvider $configurationProvider
     * @param Logger $logger
     */
    public function __construct(
        ClientFactory $clientFactory,
        Json $json,
        ConfigurationProvider $configurationProvider,
        Logger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->json = $json;
        $this->configurationProvider = $configurationProvider;
        $this->logger = $logger;
    }

    /**
     * Do request
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     * @return mixed|string
     * @throws LocalizedException
     */
    public function execute(string $uriEndpoint, array $params = [], string $requestMethod = Request::HTTP_METHOD_GET)
    {
        if (!$this->configurationProvider->getIsQwqerEnabled()) {
            throw new LocalizedException(__('qwqer integration is disabled'));
        }
        $client = $this->clientFactory->create();
        $requestedUrl = $this->getUrl($uriEndpoint, $params, $requestMethod);
        $this->setHeaders($params);

        return $this->request(
            $client,
            $requestedUrl,
            $params,
            $requestMethod
        );
    }

    /**
     * Do request
     *
     * @param Client $client
     * @param string $requestedUrl
     * @param array $params
     * @param string $requestMethod
     * @return mixed
     * @throws Exception
     */
    private function request(
        Client $client,
        string $requestedUrl,
        array $params,
        string $requestMethod = Request::HTTP_METHOD_GET
    ) {
        $statusCode = '';
        $params[RequestOptions::VERIFY] = false;
        $this->logger->debug(__('API Request to %1 [%2]', $requestedUrl, $requestMethod), $params);
        if ($this->configurationProvider->useSslVerify()) {
            $this->logger->debug(' * SSL verification turned on');
            $params[RequestOptions::VERIFY] = true;
        }

        try {
            $response = $client->request($requestMethod, $requestedUrl, $params);
            $contents = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();
            $this->logger->debug(__('API Response from %1 (%2)', $requestedUrl, $statusCode), [$contents]);
            // check if request is a success
            if (in_array($statusCode, [200, 201], true)) {
                return $this->processResponse($contents);
            }
            $errorMessage = $contents;
        } catch (GuzzleException $exception) {
            $statusCode = $exception->getCode();
            $errorMessage = $exception->getMessage();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
        $error = __(
            'Error running the API request %1 (%2 - %3): PARAMS: %4, ERROR: %5',
            $requestedUrl,
            $requestMethod,
            $statusCode,
            $this->json->serialize($params),
            $errorMessage
        );
        $this->logger->critical($error);
        if ($statusCode == 401) {
            $this->logger->debug(' * API request failed due to expired token');
        }

        return [
            "data" => [
                'statusCode' => $statusCode,
                'message' => $errorMessage
            ]
        ];
    }

    /**
     * ProcessResponse
     *
     * @param string $contents
     * @return mixed
     */
    private function processResponse(string $contents)
    {
        try {
            return $this->json->unserialize($contents);
        } catch (Exception $exception) {
            $this->logger->debug(__('Unable to JSON decode request response: %1', $exception->getMessage()));
            return $contents;
        }
    }

    /**
     * Set header for the request
     *
     * @param array $params
     * @return void
     */
    private function setHeaders(array &$params): void
    {
        $apiBearerToken = $this->configurationProvider->getApiBearerToken();

        $headers = [
            'Accept'          => 'application/json',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Cache-Control'   => 'no-cache',
            'Content-Type'    => 'application/json',
        ];

        if ($apiBearerToken) {
            $headers['Authorization'] = 'Bearer ' . $apiBearerToken;
        }

        if (isset($params['oauth'])) {
            unset($params['oauth']);
            $headers['Authorization'] = 'Bearer ' . $params['token'];
            unset($params['token']);
        }

        if (isset($params['sessionId'])) {
            $headers['Cookie'] = 'ss-id=' . $params['sessionId'];
            unset($params['sessionId']);
        }

        if (isset($params['base_auth'])) {
            unset($params['base_auth']);
            $apiUsername = $this->configurationProvider->getApiUsername();
            $apiPassword = $this->configurationProvider->getApiPassword();
            $headers['Authorization'] = 'Basic ' . base64_encode("$apiUsername:$apiPassword");
        }

        $headers = isset($params['headers']) ? array_merge($headers, $params['headers']) : $headers;
        // Remove headers which values equal 'delete-header'.
        $headers = array_filter($headers, function ($header) {
            return $header !== 'delete-header';
        });
        $params['headers'] = $headers;
    }

    /**
     * Get full URL for the API request
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $type
     * @return string
     */
    private function getUrl(string $uriEndpoint, array $params, string $type): string
    {
        $query = '';
        if (isset($params['headers'])) {
            unset($params['headers']);
        }

        if (Request::HTTP_METHOD_GET == $type) {
            if (isset($params['base_auth'])) {
                unset($params['base_auth']);
            }

            if (isset($params['oauth'])) {
                unset($params['oauth']);
            }

            if (isset($params['sessionId'])) {
                unset($params['sessionId']);
            }

            if (count($params) && (!isset($params['add_params']) || $params['add_params'])) {
                $query .= '?' . http_build_query($params);
            }
        }

        return $this->configurationProvider->getAPIBaseUrl() . $uriEndpoint . $query;
    }
}
