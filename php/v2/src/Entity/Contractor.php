<?php

declare(strict_types=1);

namespace Php\V2\Entity;

class Contractor
{
    public const TYPE_CUSTOMER = 0;
    public const CLIENT_MOBILE = 'mobile';
    public const CLIENT_WEB = 'web';

    public int $id;
    public int $type;
    public int $resellerId;
    public string $name = '';
    public string $email;
    public string $clientType;

    public function __construct(
        int $clientId,
        int $type = self::TYPE_CUSTOMER,
        string $clientType = self::CLIENT_WEB
    ) {
        $this->id = $clientId;
        $this->type = $type;
        $this->clientType = $clientType;
    }

    public static function getById(int $clientId): static
    {
        return new static($clientId); // fakes the getById method
    }

    public function getFullName(): string
    {
        return sprintf('%s %d', $this->name, $this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getClientType(): string
    {
        return $this->clientType;
    }

    public function getId(): int
    {
        return $this->id;
    }

    //mock
    public function getEmail(): string
    {
        return $this->email;
    }

    //mock
    public function getResellerId(): int
    {
        return $this->resellerId;
    }
}
