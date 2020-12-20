<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Parser;

use DOMDocument;
use DOMElement;

use function assert;
use function count;
use function preg_match;
use function str_replace;
use function stripos;
use function trim;

class LeekduckParser
{
    /**
     * @return array<string, array<int, array<string, string|int|bool|null>>>
     */
    public function parseRaidBosses(string $htmlPage): array
    {
        $domDocument = new DOMDocument();
        @$domDocument->loadHTMLFile($htmlPage);

        $raidList = $domDocument->getElementById('raid-list');
        if ($raidList === null) {
            return [];
        }

        $liItems          = $raidList->getElementsByTagName('li');
        $raids            = [];
        $currentTierLevel = 'unknown';
        foreach ($liItems as $liItem) {
            assert($liItem instanceof DOMElement);
            $attributeClass = $liItem->attributes !== null ? $liItem->attributes->getNamedItem('class') : null;
            if ($attributeClass !== null && $attributeClass->nodeValue === 'header-li') {
                $currentTierLevel = trim(str_replace('Tier', '', $liItem->textContent));
            } else {
                $bossNameValue         = trim($this->getElementsByClass($liItem, 'boss-name')[0]->nodeValue);
                [$bossName, $formName] = $this->extractFormBossName($bossNameValue);

                $bossImageContainer = $this->getElementsByClass($liItem, 'boss-img')[0] ?? null;
                $matches            = [];
                if ($bossImageContainer !== null) {
                    $bossImage = $bossImageContainer->getElementsByTagName('img')[0];
                    if ($bossImage !== null) {
                        $imgSrc = $bossImage->getAttribute('src');
                        preg_match('~pokemon_icon_(pm)?(?<dexNr>\d+)_~', $imgSrc, $matches);
                    }
                }

                $raids[$currentTierLevel][] = [
                    'name'  => trim($bossName ?? ''),
                    'dexNr' => isset($matches['dexNr']) ? (int) $matches['dexNr'] : null,
                    'level' => $currentTierLevel,
                    'shiny' => count($this->getElementsByClass($liItem, 'shiny-icon')) === 1,
                    'form'  => $formName === null ? null : trim($formName),
                ];
            }
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

    /**
     * @return array<int, string|null>
     */
    private function extractFormBossName(string $bossName): array
    {
        if (stripos($bossName, 'Mega') !== false) {
            return [$bossName, 'Mega'];
        }

        if (stripos($bossName, 'Alolan') !== false) {
            return [$bossName, 'Alola'];
        }

        if (stripos($bossName, 'Galarian') !== false) {
            return [$bossName, 'Galar'];
        }

        $matches = [];
        if (preg_match('~\((?<FormName>\w+)\)~', $bossName, $matches)) {
            return [str_replace($matches[0], '', $bossName), $matches['FormName']];
        }

        return [$bossName, null];
    }
}
