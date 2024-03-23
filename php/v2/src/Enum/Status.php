<?php

declare(strict_types=1);

namespace Php\V2\Enum;

final class Status
{
    public const COMPLETED = 'Completed';
    public const PENDING = 'Pending';
    public const REJECTED = 'Rejected';

    public static function byId(int $id): ?string
    {
        $map = [
            0 => self::COMPLETED,
            1 => self::PENDING,
            2 => self::REJECTED,
        ];

        return $map[$id] ?? null;
    }
}
