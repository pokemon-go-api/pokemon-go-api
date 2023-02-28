<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use DateTimeZone;
use stdClass;

/** @see data/raidOverwrites.xsd */
class RaidBossOverwriteStruct
{
    private const DEFAULT_TIMEZONE = 'Europe/Berlin';

    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;

    public function __construct(
        stdClass $startDate,
        stdClass $endDate,
        private string $pokemon,
        private string|null $form,
        private string $level,
        private bool $shiny,
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

    public function getLevel(): string
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
