<?php

declare(strict_types=1);

namespace Php\V2\ValueObject;

use Php\V2\Exception\OperationValidationException;

class Operation
{
    private int $resellerId;
    private int $notificationType;
    private int $clientId;
    private int $creatorId;
    private int $expertId;
    private ?OperationDifferences $differences;
    private ?int $complaintId;
    private ?int $consumptionId;
    private ?string $consumptionNumber;
    private ?string $agreementNumber;
    private ?string $date;
    private ?string $complaintNumber;

    public function __construct(
        int $resellerId,
        int $notificationType,
        int $clientId,
        int $creatorId,
        int $expertId,
        ?OperationDifferences $differences,
        ?int $complaintId,
        ?int $consumptionId,
        ?string $consumptionNumber,
        ?string $agreementNumber,
        ?string $date,
        ?string $complaintNumber
    ) {
        $this->resellerId = $resellerId;
        $this->notificationType = $notificationType;
        $this->clientId = $clientId;
        $this->creatorId = $creatorId;
        $this->expertId = $expertId;
        $this->complaintId = $complaintId;
        $this->consumptionId = $consumptionId;
        $this->consumptionNumber = $consumptionNumber;
        $this->agreementNumber = $agreementNumber;
        $this->date = $date;
        $this->complaintNumber = $complaintNumber;
        $this->differences = $differences;
    }

    public function getResellerId(): int
    {
        return $this->resellerId;
    }

    public function getNotificationType(): int
    {
        return $this->notificationType;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getExpertId(): int
    {
        return $this->expertId;
    }

    public function getCreatorId(): int
    {
        return $this->creatorId;
    }

    public function getComplaintId(): ?int
    {
        return $this->complaintId;
    }

    public function getConsumptionId(): ?int
    {
        return $this->consumptionId;
    }

    public function getConsumptionNumber(): ?string
    {
        return $this->consumptionNumber;
    }

    public function getAgreementNumber(): ?string
    {
        return $this->agreementNumber;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function getComplaintNumber(): ?string
    {
        return $this->complaintNumber;
    }

    public function getDifferences(): ?OperationDifferences
    {
        return $this->differences;
    }

    /**
     * @throws OperationValidationException
     */
    public static function fromRequestData(array $data): self
    {
        $resellerId = (int)($data['resellerId'] ?? 0);

        if ($resellerId <= 0) {
            throw new OperationValidationException(OperationValidationException::INVALID_RESELLER_ID_CODE);
        }

        $notificationType = (int)($data['notificationType'] ?? 0);
        $clientId = (int)($data['clientId'] ?? 0);
        $creatorId = (int)($data['creatorId'] ?? 0);
        $expertId = (int)($data['expertId'] ?? 0);
        $differences = (array)($data['differences'] ?? []);

        if ($notificationType <= 0) {
            throw new OperationValidationException(OperationValidationException::INVALID_NOTIFICATION_TYPE_CODE);
        }

        if ($clientId <= 0) {
            throw new OperationValidationException(OperationValidationException::INVALID_CLIENT_ID_CODE);
        }

        if ($creatorId <= 0) {
            throw new OperationValidationException(OperationValidationException::INVALID_CREATOR_ID_CODE);
        }

        if ($expertId <= 0) {
            throw new OperationValidationException(OperationValidationException::INVALID_EXPERT_ID_CODE);
        }

        return new self(
            $resellerId,
            $notificationType,
            $clientId,
            $creatorId,
            $expertId,
            !empty($differences) ? OperationDifferences::fromRequestData($differences) : null,
            (int)($data['complaintId'] ?? 0) ?: null,
            (int)($data['consumptionId'] ?? 0) ?: null,
            (string)($data['consumptionNumber'] ?? '') ?: null,
            (string)($data['agreementNumber'] ?? '') ?: null,
            (string)($data['date'] ?? '') ?: null,
            (string)($data['complaintNumber'] ?? '') ?: null
        );
    }
}
