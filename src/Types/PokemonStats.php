<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

use JsonSerializable;

final class PokemonStats implements JsonSerializable
{
    private int $stamina;
    private int $attack;
    private int $defense;

    public function __construct(int $stamina, int $attack, int $defense)
    {
        $this->stamina = $stamina;
        $this->attack  = $attack;
        $this->defense = $defense;
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

    /**
     * @return array<string, int>
     */
    public function jsonSerialize(): array
    {
        return [
            'stamina' => $this->stamina,
            'attack' => $this->attack,
            'defense' => $this->defense,
        ];
    }
}
