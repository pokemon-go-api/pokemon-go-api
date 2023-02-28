<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use DOMDocument;
use DOMElement;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function assert;
use function implode;
use function intval;
use function preg_match;
use function stripos;
use function strtoupper;

class SerebiiParser
{
    public function __construct(private PokemonCollection $pokemonCollection)
    {
    }

    public function parseRaidBosses(string $htmlPage): RaidBossCollection
    {
        $domDocument = new DOMDocument();
        @$domDocument->loadHTMLFile($htmlPage);

        $tableList  = $domDocument->getElementsByTagName('table');
        $raidTables = [];
        for ($tableCounter = 0; $tableCounter < $tableList->length; $tableCounter++) {
            $table           = $tableList->item($tableCounter);
            $tableAttributes = $table->attributes ?? null;
            if ($table === null || $tableAttributes === null) {
                continue;
            }

            $tableClassAttribute = $tableAttributes->getNamedItem('class');
            if (! $tableClassAttribute || $tableClassAttribute->nodeValue !== 'dextab') {
                continue;
            }

            $levelText       = '';
            $previousSibling = $table->previousSibling;
            if ($previousSibling !== null) {
                $previousSibling = $previousSibling->previousSibling;
            }

            if ($previousSibling !== null) {
                $levelText = $previousSibling->textContent;
            }

            switch ($levelText) {
                case '&star; List':
                    $level = RaidBoss::RAID_LEVEL_1;
                    break;
                case '&star;&star;&star; List':
                    $level = RaidBoss::RAID_LEVEL_3;
                    break;
                case '&star;&star;&star;&star;&star; List':
                    $level = RaidBoss::RAID_LEVEL_5;
                    break;
                case 'Mega Raid List':
                    $level = RaidBoss::RAID_LEVEL_MEGA;
                    break;
                default:
                    $level = null;
                    break;
            }

            if ($level === null) {
                continue;
            }

            $raidTables[$level] = $table;
        }

        $raids = new RaidBossCollection();
        foreach ($raidTables as $currentTierLevel => $table) {
            assert($table instanceof DOMElement);
            $rows = $table->childNodes;
            for ($rowCounter = 0; $rowCounter < $rows->length; $rowCounter++) {
                if ($rowCounter === 0) {
                    continue;
                }

                $tableRow  = $rows->item($rowCounter);
                $tableHtml = $domDocument->saveHTML($tableRow) ?: '';
                $matches   = [];
                if (! preg_match('~/pokemon/(?<dexNr>\d{3})(-(?<Region>.+?))?\.png~', $tableHtml, $matches)) {
                    continue;
                }

                $dexNr = intval($matches['dexNr']);
                if ($dexNr === 0) {
                    continue;
                }

                $pokemon = $basePokemon = $this->pokemonCollection->getByDexId($dexNr);
                if ($basePokemon === null || $pokemon === null) {
                    continue;
                }

                $pokemonIdParts = [$basePokemon->getId()];
                if (isset($matches['Region'])) {
                    switch ($matches['Region']) {
                        case 'a':
                            $pokemonIdParts[] = 'alola';
                            break;
                        case 'g':
                            $pokemonIdParts[] = 'galar';
                            break;
                        case 'm':
                            $pokemonIdParts[] = 'Mega';
                            break;
                        case 'mx':
                            $pokemonIdParts[] = 'Mega';
                            $pokemonIdParts[] = 'x';
                            break;
                        case 'my':
                            $pokemonIdParts[] = 'Mega';
                            $pokemonIdParts[] = 'y';
                            break;
                    }
                }

                $pokemonFormId             = strtoupper(implode('_', $pokemonIdParts));
                $pokemonTemporaryEvolution = null;
                foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
                    if ($temporaryEvolution->getId() !== $pokemonFormId) {
                        continue;
                    }

                    $pokemonTemporaryEvolution = $temporaryEvolution;
                }

                foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
                    if ($regionForm->getFormId() !== $pokemonFormId) {
                        continue;
                    }

                    $pokemon = $regionForm;
                }

                $raidboss = new RaidBoss(
                    $pokemon,
                    stripos($tableHtml, '/icons/shiny.png') !== false,
                    $currentTierLevel,
                    $pokemonTemporaryEvolution,
                );
                $raids->add($raidboss);
            }
        }

        return $raids;
    }
}
