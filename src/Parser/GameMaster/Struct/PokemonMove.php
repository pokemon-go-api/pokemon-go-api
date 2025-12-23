<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use Exception;
use PokemonGoApi\PogoAPI\Types\PokemonType;

use function preg_match;
use function str_contains;

final class PokemonMove
{
    private readonly int $id;
    private readonly string $name;
    private readonly PokemonType $pokemonType;
    private readonly float $power;
    private readonly float $energy;
    private readonly float $durationMs;
    private readonly bool $isFastMove;
    private PokemonCombatMove|null $combatMove = null;

    /**
     * @param array{
     *  templateId: string,
     *  moveSettings: array{
     *     movementId: string,
     *     pokemonType: string,
     *     power?: float,
     *     energyDelta?: float,
     *     durationMs: float
     *   }
     * } $data
     */
    public function __construct(
        array $data,
    ) {
        $moveParts = [];
        if (! preg_match('~^V(?<id>\d{4})_MOVE_(?<name>.*)$~i', $data['templateId'], $moveParts)) {
            throw new Exception('Given template id is invalid', 1766492698655);
        }

        $this->id          = (int) $moveParts['id'];
        $this->name        = $data['moveSettings']['movementId'];
        $this->pokemonType = PokemonType::createFromPokemonType(
            $data['moveSettings']['pokemonType'],
        );
        $this->power       = $data['moveSettings']['power'] ?? 0;
        $this->energy      = $data['moveSettings']['energyDelta'] ?? 0;
        $this->durationMs  = $data['moveSettings']['durationMs'];
        $this->isFastMove  = str_contains($this->name, '_FAST');
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
