<?php

declare(strict_types=1);

namespace Php\V2\Service;

use Php\V2\Exception\NotificationException;

class NotificationManager
{
    //mock
    /**
     * @throws NotificationException
     */
    public static function send(
        int $resellerId,
        int $clientId,
        string $notificationType,
        int $diffTo,
        array $templateData
    ): bool {
        return true;
    }
}
