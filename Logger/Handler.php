<?php

namespace Qwqer\Express\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * Log File name
     *
     * @var string
     */
    protected $fileName = '/var/log/qwqer.log';
}
