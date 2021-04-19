<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

final class BattleConfiguration
{
    public const NAME_EASY   = 'easy';
    public const NAME_NORMAL = 'normal';
    public const NAME_HARD   = 'hard';

    private string $name;
    private int $pokemonLevel;
    private int $friendShipLevel;

    private function __construct(
        string $name,
        int $pokemonLevel,
        int $friendShipLevel
    ) {
        $this->name            = $name;
        $this->pokemonLevel    = $pokemonLevel;
        $this->friendShipLevel = $friendShipLevel;
    }

    public static function easy(): self
    {
        return new self(self::NAME_EASY, 20, 0);
    }

    public static function normal(): self
    {
        return new self(self::NAME_NORMAL, 30, 3);
    }

    public static function hard(): self
    {
        return new self(self::NAME_HARD, 40, 4);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFriendShipLevel(): int
    {
        return $this->friendShipLevel;
    }

    public function getPokemonLevel(): int
    {
        return $this->pokemonLevel;
    }
}
