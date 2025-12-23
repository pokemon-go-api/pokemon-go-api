<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use Exception;

use function preg_match;

final readonly class PokemonCombatMove
{
    private int $moveId;
    private float $power;
    private float $energy;
    private int $durationTurns;
    private PokemonCombatMoveBuffs|null $buffs;

    /**
     * @param array{
     *  templateId: string,
     *  combatMove: array{
     *     power?: float,
     *     energyDelta?: int,
     *     durationTurns?: int,
     *     buffs?: PokemonCombatMoveBuffs|null
     *   }
     * } $data
     */
    public function __construct(
        array $data,
    ) {
        $moveParts = [];
        if (! preg_match('~^COMBAT_V(?<MoveId>[0-9]{4})_MOVE_(?<MoveName>.*)$~i', $data['templateId'], $moveParts)) {
            throw new Exception('Given template id is invalid', 1766498635686);
        }

        $this->moveId        = (int) $moveParts['MoveId'];
        $this->power         = $data['combatMove']['power'] ?? 0;
        $this->energy        = $data['combatMove']['energyDelta'] ?? 0;
        $this->durationTurns = $data['combatMove']['durationTurns'] ?? 0;
        $this->buffs         = $data['combatMove']['buffs'] ?? null;
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
