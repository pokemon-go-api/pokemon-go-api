<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

final class RaidBoss
{
    public const RAID_LEVEL_1    = 'lvl1';
    public const RAID_LEVEL_3    = 'lvl3';
    public const RAID_LEVEL_5    = 'lvl5';
    public const RAID_LEVEL_EX   = 'ex';
    public const RAID_LEVEL_MEGA = 'mega';

    private string $pokemonId;
    private bool $shinyAvailable;
    private string $raidLevel;
    private Pokemon $pokemon;
    private ?TemporaryEvolution $temporaryEvolution;

    public function __construct(
        string $pokemonId,
        bool $shinyAvailable,
        string $raidLevel,
        Pokemon $pokemon,
        ?TemporaryEvolution $temporaryEvolution
    ) {
        $this->pokemonId          = $pokemonId;
        $this->shinyAvailable     = $shinyAvailable;
        $this->raidLevel          = $raidLevel;
        $this->pokemon            = $pokemon;
        $this->temporaryEvolution = $temporaryEvolution;
    }

    public function getPokemonId(): string
    {
        if ($this->temporaryEvolution !== null) {
            return $this->temporaryEvolution->getId();
        }

        return $this->pokemonId;
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
}
