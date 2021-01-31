<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use DateTimeZone;
use stdClass;

/**
 * @see data/raidOverwrites.xsd
 */
class RaidBossOverwriteStruct
{
    private const DEFAULT_TIMEZONE = 'Europe/Berlin';

    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;
    private ?string $form;
    private string $pokemon;
    private string $level;
    private bool $shiny;

    public function __construct(
        stdClass $startDate,
        stdClass $endDate,
        string $pokemon,
        ?string $form,
        string $level,
        bool $shiny
    ) {
        $this->startDate = new DateTimeImmutable(
            $startDate->date,
            new DateTimeZone(
                $startDate->timezone ?? self::DEFAULT_TIMEZONE
            )
        );
        $this->endDate   = new DateTimeImmutable(
            $endDate->date,
            new DateTimeZone(
                $endDate->timezone ?? self::DEFAULT_TIMEZONE
            )
        );
        $this->form      = $form;
        $this->pokemon   = $pokemon;
        $this->level     = $level;
        $this->shiny     = $shiny;
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

    public function getForm(): ?string
    {
        return $this->form;
    }
}
