<?php

namespace Qwqer\Express\Controller\Api;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Qwqer\Express\Model\Api\AutocompleteAddress;
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
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Validator $formKeyValidator
     * @param AutocompleteAddress $autocompleteAddress
     * @param Json $json
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        AutocompleteAddress $autocompleteAddress,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->autocompleteAddress = $autocompleteAddress;
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
