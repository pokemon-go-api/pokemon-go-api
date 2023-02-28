<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use stdClass;

use function preg_match;
use function strpos;

final class PokemonMove
{
    private PokemonCombatMove|null $combatMove = null;

    public function __construct(
        private int $id,
        private string $name,
        private PokemonType $pokemonType,
        private float $power,
        private float $energy,
        private float $durationMs,
        private bool $isFastMove,
    ) {
    }

    public static function createFromGameMaster(stdClass $moveData): self
    {
        $moveParts = [];
        preg_match('~^V(?<id>\d{4})_MOVE_(?<name>.*)$~i', $moveData->templateId, $moveParts);

        return new self(
            (int) $moveParts['id'],
            $moveData->moveSettings->movementId,
            PokemonType::createFromPokemonType($moveData->moveSettings->pokemonType),
            $moveData->moveSettings->power ?? 0.0,
            $moveData->moveSettings->energyDelta ?? 0.0,
            $moveData->moveSettings->durationMs,
            strpos($moveData->moveSettings->movementId, '_FAST') !== false,
        );
    }

    public function setCombatMove(PokemonCombatMove $combatMove): void
    {
        $this->combatMove = $combatMove;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPokemonType(): PokemonType
    {
        return $this->pokemonType;
    }

    public function getPower(): float
    {
        return $this->power;
    }

    public function getEnergy(): float
    {
        return $this->energy;
    }

    public function getDurationMs(): float
    {
        return $this->durationMs;
    }

    public function isFastMove(): bool
    {
        return $this->isFastMove;
    }

    public function getCombatMove(): PokemonCombatMove|null
    {
        return $this->combatMove;
    }
}
