<?php

declare(strict_types=1);

namespace Php\V2\Repository;

class EmailRepository
{
    public static function getResellerEmailFrom(): string
    {
        // fakes the method
        return 'contractor@example.com';
    }

    /**
     * @return string[]
     */
    public static function getEmailsByPermit($resellerId, $event): array
    {
        // fakes the method
        return ['someemeil@example.com', 'someemeil2@example.com'];
    }
}
