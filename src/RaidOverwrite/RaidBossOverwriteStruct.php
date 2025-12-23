<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use DateTimeZone;
use PokemonGoApi\PogoAPI\Types\RaidLevel;
use stdClass;

/** @see data/raidOverwrites.xsd */
class RaidBossOverwriteStruct
{
    private const string DEFAULT_TIMEZONE = 'Europe/Berlin';

    private readonly DateTimeImmutable $startDate;

    private readonly DateTimeImmutable $endDate;

    public function __construct(
        stdClass $startDate,
        stdClass $endDate,
        private readonly string $pokemon,
        private readonly string|null $form,
        private readonly RaidLevel $level,
        private readonly bool $shiny,
    ) {
        $this->startDate = new DateTimeImmutable(
            $startDate->date,
            new DateTimeZone(
                $startDate->timezone ?? self::DEFAULT_TIMEZONE,
            ),
        );
        $this->endDate   = new DateTimeImmutable(
            $endDate->date,
            new DateTimeZone(
                $endDate->timezone ?? self::DEFAULT_TIMEZONE,
            ),
        );
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getLevel(): RaidLevel
    {
        return $this->level;
    }

    public function getPokemon(): string
    {
        return $this->pokemon;
    }

    public function isShiny(): bool
    {
        return $this->shiny;
    }

    public function getForm(): string|null
    {
        return $this->form;
    }
}
