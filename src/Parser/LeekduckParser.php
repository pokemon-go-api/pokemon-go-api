<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use DOMDocument;
use DOMElement;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use Throwable;

use function assert;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_replace;
use function similar_text;
use function str_replace;
use function stripos;
use function strtoupper;
use function trim;
use function usort;

class LeekduckParser
{
    public function __construct(private readonly PokemonCollection $pokemonCollection)
    {
    }

    public function parseRaidBosses(string $htmlPage): RaidBossCollection
    {
        $domDocument = new DOMDocument();
        @$domDocument->loadHTMLFile($htmlPage);

        $raidList = $domDocument->getElementById('raid-list');
        if ($raidList === null) {
            return new RaidBossCollection();
        }

        $liItems          = $raidList->getElementsByTagName('li');
        $raids            = new RaidBossCollection();
        $currentTierLevel = 'unknown';
        foreach ($liItems as $liItem) {
            assert($liItem instanceof DOMElement);
            $attributeClass = $liItem->attributes->getNamedItem('class');
            if ($attributeClass !== null && $attributeClass->nodeValue === 'header-li') {
                $currentTierLevelText = trim(preg_replace('~\s+~', ' ', $liItem->textContent) ?? '');
                switch (true) {
                    case stripos($currentTierLevelText, 'EX') === 0:
                        $currentTierLevel = RaidBoss::RAID_LEVEL_EX;
                        break;
                    case stripos($currentTierLevelText, 'Mega') === 0:
                        $currentTierLevel = RaidBoss::RAID_LEVEL_MEGA;
                        break;
                    case stripos($currentTierLevelText, 'Tier 5') === 0:
                        $currentTierLevel = RaidBoss::RAID_LEVEL_5;
                        break;
                    case stripos($currentTierLevelText, 'Tier 3') === 0:
                        $currentTierLevel = RaidBoss::RAID_LEVEL_3;
                        break;
                    case stripos($currentTierLevelText, 'Tier 1') === 0:
                        $currentTierLevel = RaidBoss::RAID_LEVEL_1;
                        break;
                }
            } else {
                $bossNameValue = trim($this->getElementsByClass($liItem, 'boss-name')[0]->nodeValue ?? '');
                [, $formName]  = $this->extractFormBossName($bossNameValue);

                $bossImageContainer = $this->getElementsByClass($liItem, 'boss-img')[0] ?? null;
                $pokemonImage       = null;
                if ($bossImageContainer !== null) {
                    $bossImage = $bossImageContainer->getElementsByTagName('img')[0];
                    if ($bossImage !== null) {
                        $imgSrc = $bossImage->getAttribute('src');
                        try {
                            $pokemonImage = PokemonImage::createFromFilePath($imgSrc);
                        } catch (Throwable) {
                        }
                    }
                }

                if ($pokemonImage === null) {
                    continue;
                }

                $pokemon = $basePokemon = $this->pokemonCollection->getByDexId($pokemonImage->getDexNr());
                if ($basePokemon === null || $pokemon === null) {
                    continue;
                }

                $pokemon = $pokemon->withPokemonForm(
                    new PokemonForm(
                        $pokemon->getId(),
                        $pokemon->getFormId(),
                        $pokemonImage->getAssetBundleValue(),
                        $pokemonImage->getAssetBundleSuffix(),
                    ),
                );

                $pokemonIdParts = [$basePokemon->getId()];
                if ($formName !== null) {
                    $pokemonIdParts = [...$pokemonIdParts, ...explode(' ', trim($formName))];
                }

                $pokemonFormId = strtoupper(implode('_', $pokemonIdParts));

                $raidTierLevel             = $currentTierLevel;
                $pokemonTemporaryEvolution = null;
                foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
                    if ($temporaryEvolution->getId() !== $pokemonFormId) {
                        continue;
                    }

                    $pokemonTemporaryEvolution = $temporaryEvolution;

                    if ($raidTierLevel !== RaidBoss::RAID_LEVEL_MEGA || $pokemon->getPokemonClass() === null) {
                        continue;
                    }

                    $raidTierLevel = RaidBoss::RAID_LEVEL_LEGENDARY_MEGA;
                }

                $bestMatchingRegionForms = [];
                foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
                    if ($regionForm->getFormId() !== $pokemonFormId) {
                        $scorePercent = null;
                        similar_text($regionForm->getFormId(), $pokemonFormId, $scorePercent);
                        if ($scorePercent >= 70) {
                            $bestMatchingRegionForms[] = [
                                'score' => $scorePercent,
                                'form'  => $regionForm,
                            ];
                        }

                        continue;
                    }

                    $pokemon = $regionForm;
                }

                // handle not 100% correct form names like Shellos for "east" and "west"
                // but with internal name "east_sea" and "west_sea"
                if (
                    $pokemonTemporaryEvolution === null
                    && $formName !== null
                    && $pokemon->getId() === $pokemon->getFormId()
                    && count($bestMatchingRegionForms) > 0
                ) {
                    usort($bestMatchingRegionForms, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);
                    $pokemon = $bestMatchingRegionForms[0]['form'];
                }

                if ($raidTierLevel === RaidBoss::RAID_LEVEL_5 && $pokemon->isUltraBeast()) {
                    $raidTierLevel = RaidBoss::RAID_LEVEL_ULTRA_BEAST;
                }

                $raidboss = new RaidBoss(
                    $pokemon,
                    count($this->getElementsByClass($liItem, 'shiny-icon')) === 1,
                    $raidTierLevel,
                    $pokemonTemporaryEvolution,
                    $pokemonImage->getCostume(),
                );
                $raids->add($raidboss);
            }
        }

        return $raids;
    }

    /** @return DOMElement[] */
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

    /** @return array<int, string|null> */
    private function extractFormBossName(string $bossName): array
    {
        if (stripos($bossName, 'Mega') !== false) {
            if (stripos($bossName, ' X') !== false) {
                return [$bossName, 'Mega X'];
            }

            if (stripos($bossName, ' Y') !== false) {
                return [$bossName, 'Mega Y'];
            }

            return [$bossName, 'Mega'];
        }

        if (stripos($bossName, 'Alolan') !== false) {
            return [$bossName, 'Alola'];
        }

        if (stripos($bossName, 'Galarian') !== false) {
            return [$bossName, 'Galarian'];
        }

        $matches = [];
        if (preg_match('~\((?<FormName>\w+)\)~', $bossName, $matches)) {
            return [str_replace($matches[0], '', $bossName), $matches['FormName']];
        }

        return [$bossName, null];
    }
}
