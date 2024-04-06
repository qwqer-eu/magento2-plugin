<?php

namespace Qwqer\Express\Observer\Config;

use Qwqer\Express\Model\Api\TradingPoint as ExecuteRequest;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Qwqer\Express\Provider\ConfigurationProvider;

class Save implements ObserverInterface
{
    /**
     * *
     *
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $_storeManager;

    /**
     * *
     *
     * @var ExecuteRequest
     */
    private ExecuteRequest $_apiRequestHelper;

    /**
     * *
     *
     * @var ManagerInterface
     */
    private ManagerInterface $_messageManager;

    /**
     * *
     *
     * @var RequestInterface
     */
    private RequestInterface $_request;

    /**
     * *
     *
     * @var Config
     */
    private Config $_resourceConfig;

    /**
     * *
     *
     * @param StoreManagerInterface $storeManager
     * @param ExecuteRequest $apiRequestHelper
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param Config $resourceConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ExecuteRequest $apiRequestHelper,
        ManagerInterface $messageManager,
        RequestInterface $request,
        Config $resourceConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_apiRequestHelper = $apiRequestHelper;
        $this->_messageManager = $messageManager;
        $this->_request = $request;
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $scopeId = $observer->getEvent()->getStore();
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if (!$scopeId && ($scopeId = $observer->getEvent()->getWebsite())) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        }
        if (!$scopeId) {
            $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT;
        }
        $fields = $this->_request->getParams();

        if (!empty($fields['groups']['qwqer']['fields'])) {
            $fields = $fields['groups']['qwqer']['fields'];
            $tradingPointId = $fields['trading_point_id'];
            if ($tradingPointId) {
                $workingHours = $this->_apiRequestHelper->executeRequest();
                $this->_resourceConfig->saveConfig(
                    ConfigurationProvider::API_WORKING_HOURS,
                    json_encode($workingHours),
                    $scope
                );
            }
        }
        return $this;
    }
}
