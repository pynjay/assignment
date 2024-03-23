<?php

declare(strict_types=1);

namespace Php\V2\Exception;

use Exception;

class NotificationException extends Exception
{
    private string $errorReason;

    public function __construct(string $message, string $errorReason)
    {
        parent::__construct($message);
        $this->errorReason = $errorReason;
    }

    public function getErrorReason(): string
    {
        return $this->errorReason;
    }
}
