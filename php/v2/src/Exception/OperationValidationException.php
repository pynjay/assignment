<?php

declare(strict_types=1);

namespace Php\V2\Exception;

use Exception;

class OperationValidationException extends Exception
{
    public const INVALID_RESELLER_ID_CODE = 1;
    public const INVALID_NOTIFICATION_TYPE_CODE = 2;
    public const INVALID_CLIENT_ID_CODE = 3;
    public const INVALID_CREATOR_ID_CODE = 4;
    public const INVALID_EXPERT_ID_CODE = 5;
    public const INVALID_DIFF_TO_CODE = 6;
    public const INVALID_DIFF_FROM_CODE = 7;

    private const CODE_MESSAGE_MAP = [
        self::INVALID_NOTIFICATION_TYPE_CODE => 'Invalid notificationType',
        self::INVALID_CLIENT_ID_CODE => 'Invalid clientId',
        self::INVALID_RESELLER_ID_CODE => 'Invalid resellerId',
        self::INVALID_CREATOR_ID_CODE => 'Invalid creatorId',
        self::INVALID_EXPERT_ID_CODE => 'Invalid expertId',
        self::INVALID_DIFF_TO_CODE => 'Invalid diffTo',
        self::INVALID_DIFF_FROM_CODE => 'Invalid diffFrom',
    ];

    public function __construct(int $code)
    {
        parent::__construct(
            self::CODE_MESSAGE_MAP[$code] ?? '',
            $code
        );
    }
}
