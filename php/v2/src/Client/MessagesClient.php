<?php

declare(strict_types=1);

namespace Php\V2\Client;

class MessagesClient
{
    //mock
    public static function sendMessage(
        array $body,
        int $fromId,
        string $eventType,
        ?int $clientId = null,
        ?int $diffTo = null
    ): void {
    }
}
