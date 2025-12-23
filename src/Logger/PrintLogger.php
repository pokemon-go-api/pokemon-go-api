<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Logger;

use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

use function date;
use function json_encode;
use function printf;
use function trim;

use const PHP_EOL;

class PrintLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @param array<mixed, mixed> $context */
    #[Override]
    public function log(mixed $level, mixed $message, array $context = []): void
    {
        printf(
            trim('[%s] %s %s') . PHP_EOL,
            date('H:i:s'),
            $message,
            $context !== [] ? '-> ' . json_encode($context) : '',
        );
    }
}
