<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class NoopLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @param array<mixed, mixed> $context
     */
    public function log(mixed $level, mixed $message, array $context = []): void
    {
    }
}
