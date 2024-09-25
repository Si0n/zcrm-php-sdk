<?php

namespace zcrmsdk\crm\utility;

use Psr\Log\LoggerInterface;

class DefaultLogger implements LoggerInterface
{
    protected null|string $logPath = null;

    public function __construct()
    {
        if (!ZCRMConfigUtil::getConfigValue(APIConstants::APPLICATION_LOGFILE_PATH)) {
            $dir_path = __DIR__;
            $this->logPath = match (true) {
                str_contains($dir_path, 'vendor') => substr($dir_path, 0, strpos($dir_path, 'vendor') - 1),
                default => substr($dir_path, 0, strpos($dir_path, 'src') - 1),
            };
        } else {
            $this->logPath = trim(ZCRMConfigUtil::getConfigValue(APIConstants::APPLICATION_LOGFILE_PATH));
        }
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("EMERGENCY: $message", $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("ALERT: $message", $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("CRITICAL: $message", $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("ERROR: $message", $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("WARNING: $message", $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("NOTICE: $message", $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("INFO: $message", $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("DEBUG: $message", $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->writeToFile("$level: $message", $context);
    }

    protected function writeToFile(string $msg, array $context): void
    {
        $filePointer = fopen($this->logPath . APIConstants::APPLICATION_LOGFILE_NAME, 'a');
        if (!$filePointer) {
            return;
        }
        try {
            fwrite($filePointer, sprintf("%s %s . context: %s\n", date('Y-m-d H:i:s'), $msg, json_encode($context)));
            fclose($filePointer);
        } catch (\Throwable) {
            fclose($filePointer);
        }
    }
}
