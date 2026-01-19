<?php

namespace zcrmsdk\oauth\exception;

class ZohoOAuthException extends \Exception
{
    protected $message = 'Unknown exception';
    protected $code = 0;
    protected string $file;
    protected int $line;

    public function __construct(string | \Throwable $message = 'Unknown exception', int $code = 0, ?\Throwable $previous = null)
    {
        if ($message instanceof \Throwable) {
            parent::__construct($message->getMessage(), $message->getCode(), $message);
        } else {
            parent::__construct($message, $code, $previous);
        }
    }

    public function __toString(): string
    {
        return static::class . " Caused by:'{$this->message}' in {$this->file}({$this->line})\n{$this->getTraceAsString()}";
    }
}
