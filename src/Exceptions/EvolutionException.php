<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Exceptions;

use RuntimeException;

class EvolutionException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly array $responseBody = [],
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }
}
