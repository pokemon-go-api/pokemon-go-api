<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use JsonSerializable;

final class PokemonStats implements JsonSerializable
{
    public function __construct(private int $stamina, private int $attack, private int $defense)
    {
    }

    public function getAttack(): int
    {
        return $this->attack;
    }

    public function getDefense(): int
    {
        return $this->defense;
    }

    public function getStamina(): int
    {
        return $this->stamina;
    }

    /** @return array<string, int> */
    public function jsonSerialize(): array
    {
        return [
            'stamina' => $this->stamina,
            'attack' => $this->attack,
            'defense' => $this->defense,
        ];
    }
}
