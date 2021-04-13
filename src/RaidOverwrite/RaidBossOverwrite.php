<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;
use Psr\Log\LoggerInterface;
use stdClass;

use function array_map;
use function sprintf;

class RaidBossOverwrite
{
    /** @var array<int, stdClass> $raidBossOverwrites */
    private array $raidBossOverwrites;
    private PokemonCollection $pokemonCollection;
    private LoggerInterface $logger;

    /**
     * @param array<int, stdClass> $raidBossOverwrites
     */
    public function __construct(
        array $raidBossOverwrites,
        PokemonCollection $pokemonCollection,
        LoggerInterface $logger
    ) {
        $this->raidBossOverwrites = $raidBossOverwrites;
        $this->pokemonCollection  = $pokemonCollection;
        $this->logger             = $logger;
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
                    $raidOverwrite->getPokemon()
                ));
                continue;
            }

            foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
                if ($regionForm->getFormId() !== $raidOverwrite->getForm()) {
                    continue;
                }

                $pokemon = $regionForm;
            }

            $this->logger->debug(sprintf(
                '[RaidBossOverwrite] Add RaidBoss %s',
                $pokemon->getFormId()
            ));

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
