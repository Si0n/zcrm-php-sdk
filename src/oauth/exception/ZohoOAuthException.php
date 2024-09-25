<?php

namespace zcrmsdk\oauth\exception;

use JetBrains\PhpStorm\Pure;

class ZohoOAuthException extends \Exception
{
    protected $message = 'Unknown exception';
    protected $code = 0;
    protected string $file;
    protected int $line;

    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown ' . get_class($this));
        }
        parent::__construct($message, $code);
    }

    #[Pure]
    public function __toString(): string
    {
        return get_class($this) . " Caused by:'{$this->message}' in {$this->file}({$this->line})\n{$this->getTraceAsString()}";
    }
}
