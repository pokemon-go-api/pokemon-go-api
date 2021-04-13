<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class NoopLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @param mixed               $level
     * @param string|mixed        $message
     * @param array<mixed, mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
    }
}
