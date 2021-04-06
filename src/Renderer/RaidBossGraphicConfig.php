<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Renderer;

final class RaidBossGraphicConfig
{
    public const ORDER_HIGH_TO_LOW = 'highLow';
    public const ORDER_LOW_TO_HIGH = 'lowHigh';

    private string $order;
    private bool $useShinyImages;

    public function __construct(string $order = self::ORDER_HIGH_TO_LOW, bool $useShinyImages = true)
    {
        $this->order          = $order;
        $this->useShinyImages = $useShinyImages;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function useShinyImages(): bool
    {
        return $this->useShinyImages;
    }
}
