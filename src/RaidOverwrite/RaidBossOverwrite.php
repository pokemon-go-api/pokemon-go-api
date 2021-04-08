<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;
use stdClass;

use function array_map;

class RaidBossOverwrite
{
    /** @var array<int, stdClass> $raidBossOverwrites */
    private array $raidBossOverwrites;
    private PokemonCollection $pokemonCollection;

    /**
     * @param array<int, stdClass> $raidBossOverwrites
     */
    public function __construct(
        array $raidBossOverwrites,
        PokemonCollection $pokemonCollection
    ) {
        $this->raidBossOverwrites = $raidBossOverwrites;
        $this->pokemonCollection  = $pokemonCollection;
    }

    public function overwrite(RaidBossCollection $raidBossCollection): void
    {
        $raidOverwrites = $this->parseOverwrites();
        foreach ($raidOverwrites as $raidOverwrite) {
            $endWithTolerance = $raidOverwrite->getEndDate()->modify('+90 Minutes');
            $now              = new DateTimeImmutable();

            if (
                $raidOverwrite->getEndDate() > new DateTimeImmutable('today -3 Days') &&
                $now > $endWithTolerance
            ) {
                if ($raidOverwrite->getForm() !== null) {
                    $raidBossCollection->remove($raidOverwrite->getForm());
                } else {
                    $raidBossCollection->remove($raidOverwrite->getPokemon());
                }

                continue;
            }

            if ($now < $raidOverwrite->getStartDate() || $now > $endWithTolerance) {
                continue;
            }

            $pokemon = $this->pokemonCollection->get($raidOverwrite->getPokemon());
            if ($pokemon === null) {
                continue;
            }

            foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
                if ($regionForm->getFormId() !== $raidOverwrite->getForm()) {
                    continue;
                }

                $pokemon = $regionForm;
            }

            $raidBossCollection->add(
                new RaidBoss(
                    $pokemon,
                    $raidOverwrite->isShiny(),
                    $raidOverwrite->getLevel(),
                    null,
                    null
                )
            );
        }
    }

    /**
     * @return RaidBossOverwriteStruct[]
     */
    private function parseOverwrites(): array
    {
        return array_map(
            static fn (stdClass $raidBossOverwrite): RaidBossOverwriteStruct => new RaidBossOverwriteStruct(
                $raidBossOverwrite->startDate,
                $raidBossOverwrite->endDate,
                $raidBossOverwrite->pokemon,
                $raidBossOverwrite->form ?? null,
                $raidBossOverwrite->level,
                $raidBossOverwrite->shiny === 'true',
            ),
            $this->raidBossOverwrites
        );
    }
}
