<?php

declare(strict_types=1);

namespace Php\V2\ValueObject;

use Php\V2\Exception\OperationValidationException;

class OperationDifferences
{
    private int $diffTo;
    private int $diffFrom;

    public function __construct(
        int $diffTo,
        int $diffFrom
    ) {
        $this->diffTo = $diffTo;
        $this->diffFrom = $diffFrom;
    }

    public function getTo(): int
    {
        return $this->diffTo;
    }

    public function getFrom(): int
    {
        return $this->diffFrom;
    }

    /**
     * @throws OperationValidationException
     */
    public static function fromRequestData(array $data): self
    {
        $diffTo = (int)($data['to'] ?? 0);
        $diffFrom = (int)($data['from'] ?? 0);

        if ($diffTo <= 0) {
            throw new OperationValidationException(OperationValidationException::INVALID_DIFF_TO_CODE);
        }

        if ($diffFrom <= 0) {
            throw new OperationValidationException(OperationValidationException::INVALID_DIFF_FROM_CODE);
        }

        return new self(
            $diffTo,
            $diffFrom
        );
    }
}
