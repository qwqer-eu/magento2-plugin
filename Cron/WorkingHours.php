<?php

namespace Qwqer\Express\Cron;

use Magento\Config\Model\ResourceModel\Config;
use Qwqer\Express\Model\Api\TradingPoint;
use Exception;
use Qwqer\Express\Provider\ConfigurationProvider;

class WorkingHours
{
    /**
     * *
     *
     * @var Config
     */
    private Config $_resourceConfig;

    /**
     * *
     *
     * @var TradingPoint
     */
    private TradingPoint $tradingPoint;

    /**
     * *
     *
     * @param TradingPoint $tradingPoint
     * @param Config $_resourceConfig
     */
    public function __construct(
        TradingPoint $tradingPoint,
        Config $_resourceConfig
    ) {
        $this->tradingPoint = $tradingPoint;
        $this->_resourceConfig = $_resourceConfig;
    }

    /**
     * Execute CRON job
     * @throws Exception
     */
    public function execute()
    {
        if (
            $this->tradingPoint->configurationProvider->getIsQwqerParcelEnabled()
            || $this->tradingPoint->configurationProvider->getIsQwqerDoorEnabled()
            || $this->tradingPoint->configurationProvider->getIsQwqerEnabled()
        ) {
            try {
                $workingHours = $this->tradingPoint->executeRequest();
                $this->_resourceConfig->saveConfig(
                    ConfigurationProvider::API_WORKING_HOURS,
                    json_encode($workingHours)
                );
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
}
