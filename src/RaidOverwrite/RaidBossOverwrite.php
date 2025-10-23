<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidLevel;
use Psr\Log\LoggerInterface;
use stdClass;

use function array_map;
use function sprintf;

class RaidBossOverwrite
{
    /** @param array<int, stdClass> $raidBossOverwrites */
    public function __construct(
        private readonly array $raidBossOverwrites,
        private readonly PokemonCollection $pokemonCollection,
        private readonly LoggerInterface $logger,
    ) {
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
                $pokemonId = $raidOverwrite->getPokemon();

                if ($raidOverwrite->getForm() !== null) {
                    $pokemonId = $raidOverwrite->getForm();
                }

                $this->logger->debug(sprintf('[RaidBossOverwrite] Remove existing Raidboss %s', $pokemonId));
                $raidBossCollection->remove($pokemonId);

                continue;
            }

            if ($now < $raidOverwrite->getStartDate() || $now > $endWithTolerance) {
                continue;
            }

            $pokemon = $this->pokemonCollection->get($raidOverwrite->getPokemon());
            if ($pokemon === null) {
                $this->logger->error(sprintf(
                    '[RaidBossOverwrite] Invalid Pokemon ID. Not Found in Collection %s',
                    $raidOverwrite->getPokemon(),
                ));
                continue;
            }

            foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
                if ($regionForm->getFormId() !== $raidOverwrite->getForm()) {
                    continue;
                }

                $pokemon = $regionForm;
            }

            $temporaryEvolution = null;
            foreach ($pokemon->getTemporaryEvolutions() as $megaEvolution) {
                if ($megaEvolution->getId() !== $raidOverwrite->getForm()) {
                    continue;
                }

                $temporaryEvolution = $megaEvolution;
            }

            $raidBoss =                 new RaidBoss(
                $pokemon,
                $raidOverwrite->isShiny(),
                $raidOverwrite->getLevel(),
                $temporaryEvolution,
                null,
            );

            $this->logger->debug(sprintf(
                '[RaidBossOverwrite] Add RaidBoss %s',
                $raidBoss->getPokemonWithMegaFormId(),
            ));

            $raidBossCollection->add($raidBoss);
        }
    }

    /** @return RaidBossOverwriteStruct[] */
    private function parseOverwrites(): array
    {
        return array_map(
            static fn (stdClass $raidBossOverwrite): RaidBossOverwriteStruct => new RaidBossOverwriteStruct(
                $raidBossOverwrite->startDate,
                $raidBossOverwrite->endDate,
                $raidBossOverwrite->pokemon,
                $raidBossOverwrite->form ?? null,
                RaidLevel::from($raidBossOverwrite->level),
                $raidBossOverwrite->shiny === 'true',
            ),
            $this->raidBossOverwrites,
        );
    }
}
