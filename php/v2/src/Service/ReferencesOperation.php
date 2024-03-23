<?php

declare(strict_types=1);

namespace Php\V2\Service;

abstract class ReferencesOperation
{
    abstract public function doOperation(): array;

    public function getRequest(string $pName): array
    {
        return (array)($_REQUEST[$pName] ?? []);
    }
}
