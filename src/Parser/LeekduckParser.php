<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidLevel;
use Throwable;

use function assert;
use function count;
use function explode;
use function implode;
use function in_array;
use function preg_match;
use function similar_text;
use function str_replace;
use function str_starts_with;
use function stripos;
use function strtolower;
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

        $xpath         = new DOMXPath($domDocument);
        $raidListItems = $xpath->query(
            "//div[contains(@class, 'raid-bosses')] | //div[contains(@class, 'shadow-raid-bosses')]",
        );
        if (! $raidListItems || $raidListItems->length === 0) {
            return new RaidBossCollection();
        }

        $raids = new RaidBossCollection();
        foreach ($raidListItems->getIterator() as $raidList) {
            assert($raidList instanceof DOMElement);
            $tiers = $this->getElementsByClass($raidList, 'tier');

            foreach ($tiers as $tierContainer) {
                $raidTierLevel = $this->extractTierLevel($tierContainer);

                foreach ($this->getElementsByClass($tierContainer, 'card') as $cardContainer) {
                    $pokemonImage = $this->extractPokemonImage($cardContainer);
                    $isShiny      = count($this->getElementsByClass($cardContainer, 'shiny-icon')) > 0;

                    if ($pokemonImage === null) {
                        continue;
                    }

                    [, $formName] = $this->extractFormBossName($cardContainer);

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

                    $pokemonFormId          = strtoupper(implode('_', $pokemonIdParts));
                    $pokemonFormIdWithAsset = strtoupper(implode('_', [
                        ...$pokemonIdParts,
                        $pokemonImage->getAssetBundleSuffix(),
                    ]));

                    $pokemonTemporaryEvolution = null;
                    foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
                        if ($temporaryEvolution->getId() !== $pokemonFormId) {
                            continue;
                        }

                        $pokemonTemporaryEvolution = $temporaryEvolution;

                        if ($raidTierLevel !== RaidLevel::RaidMega || $pokemon->getPokemonClass() === null) {
                            continue;
                        }

                        $raidTierLevel = RaidLevel::RaidLegendaryMega;
                    }

                    $bestMatchingRegionForms = [];
                    foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
                        if (! in_array($regionForm->getFormId(), [$pokemonFormId, $pokemonFormIdWithAsset], true)) {
                            $scorePercent = null;
                            similar_text($regionForm->getFormId(), $pokemonFormId, $scorePercent);
                            if ($scorePercent >= 70) {
                                $bestMatchingRegionForms[] = [
                                    'score' => $scorePercent,
                                    'form' => $regionForm,
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
                        usort(
                            $bestMatchingRegionForms,
                            static fn (array $a, array $b): int => $b['score'] <=> $a['score'],
                        );
                        $pokemon = $bestMatchingRegionForms[0]['form'];
                    }

                    if ($raidTierLevel === RaidLevel::Raid5 && $pokemon->isUltraBeast()) {
                        $raidTierLevel = RaidLevel::RaidUltraBeast;
                    }

                    $raidboss = new RaidBoss(
                        $pokemon,
                        $isShiny,
                        $raidTierLevel,
                        $pokemonTemporaryEvolution,
                        $pokemonImage->getCostume(),
                    );
                    $raids->add($raidboss);
                }
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
    private function extractFormBossName(DOMElement $container): array
    {
        $bossName = $this->extractBossName($container);
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

        if (stripos($bossName, 'Hisuian') !== false) {
            return [$bossName, 'Hisuian'];
        }

        $matches = [];
        if (preg_match('~\((?<FormName>\w+)\)~', $bossName, $matches)) {
            return [str_replace($matches[0], '', $bossName), $matches['FormName']];
        }

        return [$bossName, null];
    }

    public function extractTierLevel(DOMElement $tierContainer): RaidLevel
    {
        $isShadow    = false;
        $tierLevel   = null;
        $header      = null;
        $tierHeaders = $tierContainer->getElementsByTagName('h2');
        foreach ($tierHeaders as $header) {
            if ($header->getAttribute('class') === 'header') {
                $tierLevel = $header->getAttribute('data-tier');
                $isShadow  = str_starts_with($header->nodeValue ?? '', 'Shadow ');
                break;
            }
        }

        if ($isShadow) {
            return match ($tierLevel) {
                '1' => RaidLevel::ShadowRaid1,
                '3' => RaidLevel::ShadowRaid3,
                '5' => RaidLevel::ShadowRaid5,
                default => throw new Exception(
                    'Can not extract shadow raid tier level from ' . $header?->nodeValue,
                )
            };
        }

        return match (strtolower($tierLevel ?? '')) {
            '1' => RaidLevel::Raid1,
            '3' => RaidLevel::Raid3,
            '5' => RaidLevel::Raid5,
            'mega' => RaidLevel::RaidMega,
            default => throw new Exception('Can not extract raid tier level from ' . $header?->nodeValue)
        };
    }

    public function extractBossName(DOMElement $tierContainer): string
    {
        $bossNamesElems = $tierContainer->getElementsByTagName('p');
        foreach ($bossNamesElems as $elem) {
            if ($elem->getAttribute('class') === 'name' || $elem->getAttribute('class') === 'name small-type') {
                return trim($elem->nodeValue ?? '');
            }
        }

        return '';
    }

    public function extractPokemonImage(DOMElement $card): PokemonImage|null
    {
        $bossImageContainer = $this->getElementsByClass($card, 'boss-img')[0] ?? null;
        if ($bossImageContainer === null) {
            return null;
        }

        $pokemonImage = null;
        $bossImage    = $bossImageContainer->getElementsByTagName('img')[0];
        if ($bossImage !== null) {
            $imgSrc = $bossImage->getAttribute('src');
            try {
                $pokemonImage = PokemonImage::createFromFilePath($imgSrc);
            } catch (Throwable) {
            }
        }

        return $pokemonImage;
    }
}
