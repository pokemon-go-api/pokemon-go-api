<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

final class RaidBoss
{
    public const RAID_LEVEL_1              = 'lvl1';
    public const RAID_LEVEL_3              = 'lvl3';
    public const RAID_LEVEL_5              = 'lvl5';
    public const RAID_LEVEL_EX             = 'ex';
    public const RAID_LEVEL_MEGA           = 'mega';
    public const RAID_LEVEL_LEGENDARY_MEGA = 'legendary_mega';
    public const RAID_LEVEL_ULTRA_BEAST    = 'ultra_beast';

    private bool $shinyAvailable;
    private string $raidLevel;
    private Pokemon $pokemon;
    private ?TemporaryEvolution $temporaryEvolution;
    private ?string $costumeId;
    /** @var array<int, BattleResult> */
    private array $battleResults = [];

    public function __construct(
        Pokemon $pokemon,
        bool $shinyAvailable,
        string $raidLevel,
        ?TemporaryEvolution $temporaryEvolution,
        ?string $costumeId = null
    ) {
        $this->shinyAvailable     = $shinyAvailable;
        $this->raidLevel          = $raidLevel;
        $this->pokemon            = $pokemon;
        $this->temporaryEvolution = $temporaryEvolution;
        $this->costumeId          = $costumeId;
    }

    public function getPokemonWithMegaFormId(): string
    {
        if ($this->temporaryEvolution !== null) {
            return $this->temporaryEvolution->getId();
        }

        return $this->getPokemon()->getFormId();
    }

    public function getCostumeId(): ?string
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

    public function getTemporaryEvolution(): ?TemporaryEvolution
    {
        return $this->temporaryEvolution;
    }

    /** @return array<int, BattleResult> */
    public function getBattleResults(): array
    {
        return $this->battleResults;
    }

    public function getPokemonImage(): ?PokemonImage
    {
        return $this->getPokemon()->getPokemonImage($this->temporaryEvolution, $this->costumeId);
    }

    public function setBattleResults(BattleResult ...$battleResults): void
    {
        $this->battleResults = $battleResults;
    }
}
