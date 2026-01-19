<?php

namespace zcrmsdk\crm\exception;

class ZCRMException extends \Exception
{
    protected $message = 'Unknown exception';
    protected $code = 0;
    protected string $file;
    protected int $line;

    private string $exceptionCode = 'Unknown';
    private array $exceptionDetails = [];

    public function __construct(string $message = 'Unknown exception', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return static::class . " Caused by:'{$this->message}' in {$this->file}({$this->line})\n{$this->getTraceAsString()}";
    }

    public function getExceptionCode(): string
    {
        return $this->exceptionCode;
    }

    public function setExceptionCode(string $exceptionCode): void
    {
        $this->exceptionCode = $exceptionCode;
    }

    public function getExceptionDetails(): array
    {
        return $this->exceptionDetails;
    }

    public function setExceptionDetails(array $exceptionDetails): void
    {
        $this->exceptionDetails = $exceptionDetails;
    }
}
