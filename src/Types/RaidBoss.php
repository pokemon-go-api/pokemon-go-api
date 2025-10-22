<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use function array_values;

final class RaidBoss
{
    /** @var array<int, BattleResult> */
    private array $battleResults = [];

    public function __construct(
        private readonly Pokemon $pokemon,
        private readonly bool $shinyAvailable,
        private readonly RaidLevel $raidLevel,
        private readonly TemporaryEvolution|null $temporaryEvolution,
        private readonly string|null $costumeId = null,
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

    public function getRaidLevel(): RaidLevel
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
