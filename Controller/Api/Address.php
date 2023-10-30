<?php

namespace Qwqer\Express\Controller\Api;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Qwqer\Express\Model\Api\AutocompleteAddress;
use Qwqer\Express\Model\Api\ParcelMachines;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Serialize\Serializer\Json;
use \Magento\Framework\App\Action\Action;

/**
 * Address controller
 */
class Address extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var Validator
     */
    private Validator $formKeyValidator;

    /**
     * @var AutocompleteAddress
     */
    protected AutocompleteAddress $autocompleteAddress;

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @var ParcelMachines
     */
    private ParcelMachines $parcelMachines;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Validator $formKeyValidator
     * @param AutocompleteAddress $autocompleteAddress
     * @param ParcelMachines $parcelMachines
     * @param Json $json
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        AutocompleteAddress $autocompleteAddress,
        ParcelMachines $parcelMachines,
        Json $json
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->autocompleteAddress = $autocompleteAddress;
        $this->parcelMachines = $parcelMachines;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * Address execute
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        $result = [];
        try {
            if ($this->getRequest()->getParam('address')) {
                $params = ['input' => $this->getRequest()->getParam('address')];
                $result = $this->autocompleteAddress->executeRequest($params);
            }
            if ($this->getRequest()->getParam('parcels')) {
                $result = $this->parcelMachines->getDataForDropdown();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to update autocomplete Address. Error: %1', $e->getMessage())
            );
        }

        return $this->resultJsonFactory->create()->setData(
            $this->json->serialize($result)
        );
    }
}
