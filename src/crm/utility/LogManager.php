<?php

namespace zcrmsdk\crm\utility;

use Psr\Log\LoggerInterface;

class LogManager
{
    protected static null|LoggerInterface $logger = null;

    public static function warn(string $msg, array $context = []): void
    {
        self::getLogger()->warning($msg, $context);
    }

    public static function info(string $msg, array $context = []): void
    {
        self::getLogger()->info($msg, $context);
    }

    public static function severe(string $msg, array $context = []): void
    {
        self::getLogger()->error($msg, $context);
    }

    public static function err(string $msg, array $context = []): void
    {
        self::getLogger()->error($msg, $context);
    }

    public static function debug(string $msg, array $context = []): void
    {
        self::getLogger()->debug($msg, $context);
    }

    protected static function getLogger(): LoggerInterface
    {
        if (null === self::$logger) {
            self::$logger = match (true) {
                ZCRMConfigUtil::getConfigValue(APIConstants::APPLICATION_LOGGER_INSTANCE) instanceof LoggerInterface => ZCRMConfigUtil::getConfigValue(APIConstants::APPLICATION_LOGGER_INSTANCE),
                default => new DefaultLogger(),
            };
        }

        return self::$logger;
    }
}
