<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Parser;

use DOMDocument;
use DOMElement;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;

use function assert;
use function count;
use function preg_match;
use function stripos;

class PokefansNetParser
{
    private PokemonCollection $pokemonCollection;

    public function __construct(PokemonCollection $pokemonCollection)
    {
        $this->pokemonCollection = $pokemonCollection;
    }

    public function parseRaidBosses(string $htmlPage): RaidBossCollection
    {
        $domDocument = new DOMDocument();
        @$domDocument->loadHTMLFile($htmlPage);

        $contentArticle = $domDocument->getElementById('content');
        assert($contentArticle !== null);

        $raidBosses = $this->getElementsByClass($contentArticle, 'pokemon-container');

        $raids = new RaidBossCollection();
        foreach ($raidBosses as $raidBossContainer) {
            assert($raidBossContainer instanceof DOMElement);

            $raidBossHtml = $domDocument->saveHTML($raidBossContainer) ?: '';

            if (count($this->getElementsByClass($raidBossContainer, 'demnachst')) > 0) {
                continue;
            }

            $matches = [];
            if (! preg_match('~/pokemon-go/modelle/(?<dexNr>\d{3})(-(?<Region>.+?))?\.png~', $raidBossHtml, $matches)) {
                continue;
            }

            $dexNr         = (int) $matches['dexNr'];
            $assetBundleId = ($matches['Region'] ?? 0);
            if ($dexNr === 0) {
                continue;
            }

            $pokemon = $basePokemon = $this->pokemonCollection->getByDexId($dexNr);
            if ($basePokemon === null || $pokemon === null) {
                continue;
            }

            $raidInfoContainer = $this->getElementsByClass($raidBossContainer, 'raid-info')[0];
            $raidImages        = $raidInfoContainer->getElementsByTagName('img')->length;

            if (stripos($raidBossHtml, '/mega-flame.png') !== false) {
                $currentTierLevel = RaidBoss::RAID_LEVEL_MEGA;
            } elseif ($raidImages === 5) {
                $currentTierLevel = RaidBoss::RAID_LEVEL_5;
            } elseif ($raidImages === 3) {
                $currentTierLevel = RaidBoss::RAID_LEVEL_3;
            } else {
                $currentTierLevel = RaidBoss::RAID_LEVEL_1;
            }

            $pokemonTemporaryEvolution = null;
            foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
                if ($temporaryEvolution->getAssetsBundleId() !== (int) $assetBundleId) {
                    continue;
                }

                $pokemonTemporaryEvolution = $temporaryEvolution;
            }

            foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
                if ($regionForm->getAssetsBundleId() === (int) $assetBundleId) {
                    $pokemon = $regionForm;
                }

                if ($regionForm->getAssetBundleSuffix() !== $assetBundleId) {
                    continue;
                }

                $pokemon = $regionForm;
            }

            $raidboss = new RaidBoss(
                $pokemon,
                stripos($raidBossHtml, 'ic_shiny.png') !== false,
                $currentTierLevel,
                $pokemonTemporaryEvolution
            );
            $raids->add($raidboss);
        }

        return $raids;
    }

    /**
     * @return DOMElement[]
     */
    private function getElementsByClass(DOMElement $node, string $className): array
    {
        $nodes = [];

        $childNodeList = $node->getElementsByTagName('*');
        for ($i = 0; $i < $childNodeList->length; $i++) {
            $temp = $childNodeList->item($i);
            if ($temp === null) {
                continue;
            }

            if (stripos($temp->getAttribute('class'), $className) === false) {
                continue;
            }

            $nodes[] = $temp;
        }

        return $nodes;
    }
}
