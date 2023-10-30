<?php

namespace Qwqer\Express\Controller\Adminhtml\Address;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Qwqer\Express\Model\Api\AutocompleteAddress;
use Qwqer\Express\Model\Api\GeoCode;

class Autocomplete extends Action
{
    public const ADMIN_RESOURCE = 'Qwqer_Express::config';

    /**
     * @var AutocompleteAddress
     */
    private AutocompleteAddress $autocompleteAddress;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var GeoCode
     */
    protected GeoCode $geoCode;

    /**
     * Execute constructor
     *
     * @param Context $context
     * @param AutocompleteAddress $autocompleteAddress
     * @param JsonFactory $resultJsonFactory
     * @param GeoCode $geoCode
     */
    public function __construct(
        Context $context,
        AutocompleteAddress $autocompleteAddress,
        JsonFactory $resultJsonFactory,
        GeoCode $geoCode
    ) {
        parent::__construct($context);
        $this->autocompleteAddress = $autocompleteAddress;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->geoCode = $geoCode;
    }

    /**
     * Autocomplete execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        $result = [];
        try {
            if ($this->_request->getParam('address')) {
                $params = ['input' => $this->_request->getParam('address')];
                $result = $this->autocompleteAddress->executeRequest($params);
            }
            if ($this->_request->getParam('location')) {
                $params = ['address' => $this->_request->getParam('location')];
                $location = $this->geoCode->executeRequest($params);
                if (!empty($location['coordinates'])) {
                    $result = $location['coordinates'];
                }
            }
        } catch (Exception $e) {
            //skip log
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
