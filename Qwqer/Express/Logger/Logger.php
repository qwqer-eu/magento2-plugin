<?php

namespace Qwqer\Express\Logger;

use Qwqer\Express\Provider\ConfigurationProvider;
use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{

    /**
     * @var ConfigurationProvider
     */
    protected ConfigurationProvider $configurationProvider;

    /**
     * @param ConfigurationProvider $configurationProvider
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        ConfigurationProvider $configurationProvider,
        string $name = 'QwqerCoreLogger',
        array $handlers = [],
        array $processors = []
    ) {
        $this->configurationProvider = $configurationProvider;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function debug($message, array $context = []): void
    {
        $this->addRecord(static::DEBUG, (string) $message, $context);
    }
}
