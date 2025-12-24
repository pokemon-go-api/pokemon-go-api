<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use Dom\HTMLDocument;
use PokemonGoApi\PogoAPI\Collections\MaxBattleCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Pokemon;
use PokemonGoApi\PogoAPI\Types\MaxBattle;
use PokemonGoApi\PogoAPI\Types\MaxBattleLevel;
use tidy;

use function preg_match;

class SnacknapParser
{
    public function __construct(private readonly PokemonCollection $pokemonCollection)
    {
    }

    public function parseMaxBattle(string $htmlPage): MaxBattleCollection
    {
        $maxBattleCollection = new MaxBattleCollection();
        $tidy                = new tidy();
        $tidy->parseFile($htmlPage);
        $tidy->cleanRepair();

        $domDocument = HTMLDocument::createFromString($tidy->value ?? '');

        $rows = $domDocument->getElementById('pokemon')?->getElementsByClassName('row') ?? [];

        $tierLevel = MaxBattleLevel::LEVEL_1;
        foreach ($rows as $row) {
            if (preg_match('~Tier (?<tierLevel>\d)~', $row->textContent ?? '', $matches)) {
                $tierLevel = MaxBattleLevel::from((int) $matches['tierLevel']);
                continue;
            }

            $pokemonLinks = $row->getElementsByTagName('a');
            foreach ($pokemonLinks as $pokemonLink) {
                if (! $pokemonLink->hasAttribute('href')) {
                    continue;
                }

                $isShiny = $pokemonLink->getElementsByClassName('shiny')->count() === 1;
                $href    = $pokemonLink->getAttribute('href') ?? '';
                if (! preg_match('~(?<dexNr>\d+)$~', $href, $matches)) {
                    continue;
                }

                $pokemonNumber = (int) $matches['dexNr'];
                $pokemon       = $this->pokemonCollection->getByDexId($pokemonNumber);
                if (! $pokemon instanceof Pokemon) {
                    continue;
                }

                $maxBattleCollection->add(
                    new MaxBattle(
                        $pokemon,
                        $isShiny,
                        $tierLevel,
                    ),
                );
            }
        }

        return $maxBattleCollection;
    }
}
