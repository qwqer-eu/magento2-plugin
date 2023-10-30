<?php

namespace Qwqer\Express\Model\Api;

use Exception;
use Qwqer\Express\Model\Api\AbstractRequest;

class ParcelMachines extends AbstractRequest
{
    public $parcels;

    /**
     * ParcelMachines getResponse
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
        $items = [];

        if (!empty($response['data'])) {
            if ( isset($response['data']['statusCode'])
                && !in_array($response['data']['statusCode'], [200, 201], true)
            ) {
                return $items;
            }

            if(!empty($response['data']['omniva'])) {
                $this->parcels = $response['data']['omniva'];
                return $this->parcels;
            }
        }

        return $items;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function getDataForDropdown()
    {
        if(empty($this->parcels)) {
            $this->parcels = $this->executeRequest();
        }
        $parcelsArray = [];
        if (!empty($this->parcels)) {
            foreach ($this->parcels as $item) {
                $parcelsArray[] = [
                    'label' => $item['name'],
                    'value' => $item['id']
                ];
            }
        }
        return $parcelsArray;
    }

    /**
     * @param $parcelName
     * @return mixed|void
     * @throws Exception
     */
    public function getParcelDataByName($parcelName)
    {
        if(empty($this->parcels)) {
            $this->parcels = $this->executeRequest();
        }
        foreach ($this->parcels as $item) {
            if($item['name'] == $parcelName){
                return $item;
            }
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
        return $this->configurationProvider->getParcelMachinesUrl();
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
