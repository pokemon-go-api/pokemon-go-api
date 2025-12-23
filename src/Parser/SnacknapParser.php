<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use Dom\HTMLDocument;
use PokemonGoApi\PogoAPI\Collections\MaxBattleCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Types\MaxBattle;
use PokemonGoApi\PogoAPI\Types\MaxBattleLevel;

use function preg_match;

class SnacknapParser
{
    public function __construct(private readonly PokemonCollection $pokemonCollection)
    {
    }

    public function parseMaxBattle(string $htmlPage): MaxBattleCollection
    {
        $maxBattleCollection = new MaxBattleCollection();
        $tidy = tidy_parse_file($htmlPage);
        $tidy->cleanRepair();
        $domDocument         = HTMLDocument::createFromString((string) $tidy);

        $rows = $domDocument->getElementById('pokemon')->getElementsByClassName('row');

        $tierLevel = null;
        foreach ($rows as $row) {
            if (preg_match('~Tier (?<tierLevel>\d)~', $row->textContent, $matches)) {
                $tierLevel = MaxBattleLevel::tryFrom((int) $matches['tierLevel']);
                continue;
            }

            $pokemonLinks = $row->getElementsByTagName('a');
            foreach ($pokemonLinks as $pokemonLink) {
                if (! $pokemonLink->hasAttribute('href')) {
                    continue;
                }

                $isShiny = $pokemonLink->getElementsByClassName('shiny')->count() === 1;
                $href    = $pokemonLink->getAttribute('href');
                preg_match('~(?<dexNr>\d+)$~', $href, $matches);
                $pokemonNumber = (int) $matches['dexNr'];
                $pokemon       = $this->pokemonCollection->getByDexId($pokemonNumber);
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
