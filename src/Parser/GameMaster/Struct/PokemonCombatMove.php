<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;
use Exception;

use function preg_match;

final readonly class PokemonCombatMove
{
    public function __construct(
        private int $moveId,
        private float $power,
        private float $energy,
        private int $durationTurns,
        private PokemonCombatMoveBuffs|null $buffs,
    ) {
    }

    /** @param array{ power?: float, energyDelta?: int, durationTurns?: int, buffs?: PokemonCombatMoveBuffs|null } $combatMove */
    #[Constructor]
    public static function fromArray(
        string $templateId,
        array $combatMove,
    ): self {
        $moveParts = [];
        if (! preg_match('~^COMBAT_V(?<MoveId>[0-9]{4})_MOVE_(?<MoveName>.*)$~i', $templateId, $moveParts)) {
            throw new Exception('Given template id is invalid', 1766498635686);
        }

        return new self(
            (int) $moveParts['MoveId'],
            $combatMove['power'] ?? 0,
            $combatMove['energyDelta'] ?? 0,
            $combatMove['durationTurns'] ?? 0,
            $combatMove['buffs'] ?? null,
        );
    }

    public function getMoveId(): int
    {
        return $this->moveId;
    }

    public function getPower(): float
    {
        return $this->power;
    }

    public function getEnergy(): float
    {
        return $this->energy;
    }

    public function getDurationTurns(): int
    {
        return $this->durationTurns;
    }

    public function getBuffs(): PokemonCombatMoveBuffs|null
    {
        return $this->buffs;
    }
}
