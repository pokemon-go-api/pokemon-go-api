<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use function array_values;

final class RaidBoss
{
    public const RAID_LEVEL_1              = 'lvl1';
    public const RAID_LEVEL_3              = 'lvl3';
    public const RAID_LEVEL_5              = 'lvl5';
    public const RAID_LEVEL_EX             = 'ex';
    public const RAID_LEVEL_MEGA           = 'mega';
    public const RAID_LEVEL_LEGENDARY_MEGA = 'legendary_mega';
    public const RAID_LEVEL_ULTRA_BEAST    = 'ultra_beast';

    /** @var array<int, BattleResult> */
    private array $battleResults = [];

    public function __construct(
        private Pokemon $pokemon,
        private bool $shinyAvailable,
        private string $raidLevel,
        private TemporaryEvolution|null $temporaryEvolution,
        private string|null $costumeId = null,
    ) {
    }

    public function getPokemonWithMegaFormId(): string
    {
        if ($this->temporaryEvolution !== null) {
            return $this->temporaryEvolution->getId();
        }

        return $this->getPokemon()->getFormId();
    }

    public function getCostumeId(): string|null
    {
        return $this->costumeId;
    }

    public function getPokemon(): Pokemon
    {
        return $this->pokemon;
    }

    public function getRaidLevel(): string
    {
        return $this->raidLevel;
    }

    public function isShinyAvailable(): bool
    {
        return $this->shinyAvailable;
    }

    public function getTemporaryEvolution(): TemporaryEvolution|null
    {
        return $this->temporaryEvolution;
    }

    /** @return array<int, BattleResult> */
    public function getBattleResults(): array
    {
        return $this->battleResults;
    }

    public function getPokemonImage(): PokemonImage|null
    {
        return $this->getPokemon()->getPokemonImage($this->temporaryEvolution, $this->costumeId);
    }

    public function setBattleResults(BattleResult ...$battleResults): void
    {
        $this->battleResults = array_values($battleResults);
    }
}
