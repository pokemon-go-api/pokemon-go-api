<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use stdClass;

use function preg_match;
use function str_contains;

final class PokemonMove
{
    private PokemonCombatMove|null $combatMove = null;

    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly PokemonType $pokemonType,
        private readonly float $power,
        private readonly float $energy,
        private readonly float $durationMs,
        private readonly bool $isFastMove,
    ) {
    }

    public static function createFromGameMaster(stdClass $moveData): self
    {
        $moveParts = [];
        preg_match('~^V(?<id>\d{4})_MOVE_(?<name>.*)$~i', (string) $moveData->templateId, $moveParts);

        return new self(
            (int) $moveParts['id'],
            (string) $moveData->moveSettings->movementId,
            PokemonType::createFromPokemonType($moveData->moveSettings->pokemonType),
            $moveData->moveSettings->power ?? 0.0,
            $moveData->moveSettings->energyDelta ?? 0.0,
            $moveData->moveSettings->durationMs,
            str_contains((string) $moveData->moveSettings->movementId, '_FAST'),
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
