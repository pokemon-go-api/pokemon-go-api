<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use JsonSerializable;
use Override;

final readonly class PokemonStats implements JsonSerializable
{
    private int $stamina;
    private int $attack;
    private int $defense;

    public function __construct(
        int $baseStamina,
        int $baseAttack,
        int $baseDefense,
    ) {
        $this->stamina = $baseStamina;
        $this->attack  = $baseAttack;
        $this->defense = $baseDefense;
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
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'stamina' => $this->stamina,
            'attack' => $this->attack,
            'defense' => $this->defense,
        ];
    }
}
