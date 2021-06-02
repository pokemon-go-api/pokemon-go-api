<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer;

use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Renderer\Types\RaidBossGraphic;
use PokemonGoApi\PogoAPI\Renderer\Types\RaidBossGraphicConfig;
use PokemonGoApi\PogoAPI\Types\PokemonStats;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\WeatherBoost;
use PokemonGoApi\PogoAPI\Util\CpCalculator;
use PokemonGoApi\PogoAPI\Util\TypeEffectivenessCalculator;
use PokemonGoApi\PogoAPI\Util\TypeWeatherCalculator;

use function array_filter;
use function array_map;
use function array_reverse;
use function count;
use function max;
use function number_format;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function sprintf;
use function trim;

final class RaidBossGraphicRenderer
{
    public function buildGraphic(
        RaidBossCollection $raidBossCollection,
        TranslationCollection $translationCollection,
        RaidBossGraphicConfig $raidBossGraphicConfig
    ): RaidBossGraphic {
        $weatherCalculator = new TypeWeatherCalculator();
        $typeCalculator    = new TypeEffectivenessCalculator();
        $bosses            = [];

        $raidBossList = $raidBossCollection->toArray();
        if ($raidBossGraphicConfig->getOrder() === RaidBossGraphicConfig::ORDER_LOW_TO_HIGH) {
            $raidBossList = array_reverse($raidBossList);
        }

        foreach ($raidBossList as $raidBoss) {
            $raidBossPokemon = $raidBoss->getPokemon();

            switch ($raidBoss->getRaidLevel()) {
                case RaidBoss::RAID_LEVEL_EX:
                    $levelIcon = '2';
                    break;
                case RaidBoss::RAID_LEVEL_MEGA:
                    $levelIcon = '3';
                    break;
                case RaidBoss::RAID_LEVEL_5:
                    $levelIcon = '2';
                    break;
                case RaidBoss::RAID_LEVEL_3:
                    $levelIcon = '1';
                    break;
                case RaidBoss::RAID_LEVEL_1:
                    $levelIcon = '0';
                    break;
                default:
                    $levelIcon = '';
                    break;
            }

            $temporaryEvolution = $raidBoss->getTemporaryEvolution();
            $assetsBundleId     = $raidBossPokemon->getAssetsBundleId();
            $raidBossTypes      = [
                $raidBossPokemon->getTypePrimary(),
                $raidBossPokemon->getTypeSecondary(),
            ];
            if ($temporaryEvolution !== null) {
                $assetsBundleId = $temporaryEvolution->getAssetsBundleId();
                $raidBossTypes  = [
                    $temporaryEvolution->getTypePrimary(),
                    $temporaryEvolution->getTypeSecondary(),
                ];
            }

            $raidBossTypeNames = array_map(
                static fn (PokemonType $pokemonType): string => $pokemonType->getType(),
                array_filter(
                    $raidBossTypes,
                    static fn (PokemonType $pokemonType): bool => $pokemonType->getType() !== PokemonType::NONE
                )
            );

            $raidBossStats = $raidBossPokemon->getStats() ?: new PokemonStats(0, 0, 0);

            $raidData = [
                'id'           => $raidBoss->getPokemon()->getId(),
                'form'         => $raidBoss->getPokemonId(),
                'level'        => $raidBoss->getRaidLevel(),
                'levelIcon'    => $levelIcon,
                'name'         => $this->getName($raidBoss, $translationCollection),
                'shiny'        => $raidBoss->isShinyAvailable(),
                'image'        => $raidBoss->getPokemonImage()->buildUrl(
                    $raidBoss->isShinyAvailable() && $raidBossGraphicConfig->useShinyImages()
                ),
                'types'        => $raidBossTypeNames,
                'counter'      => $typeCalculator->getAllEffectiveTypes(...$raidBossTypes),
                'weather'      => array_map(
                    static fn (WeatherBoost $weatherBoost): string => $weatherBoost->getAssetsName(),
                    $weatherCalculator->getWeatherBoost(...$raidBossTypes)
                ),
                'cpRange'      => sprintf(
                    '%d–%d',
                    CpCalculator::calculateRaidMinCp($raidBossStats),
                    CpCalculator::calculateRaidMaxCp($raidBossStats)
                ),
                'cpRangeBoost' => sprintf(
                    '%d–%d',
                    CpCalculator::calculateRaidWeatherBoostMinCp($raidBossStats),
                    CpCalculator::calculateRaidWeatherBoostMaxCp($raidBossStats)
                ),
            ];

            $battleResults = $raidBoss->getBattleResults();
            if (count($battleResults) > 0) {
                foreach ($battleResults as $battleResult) {
                    $raidData['battleResult'][$battleResult->getBattleConfiguration()->getName()] = number_format(
                        max($battleResult->getTotalEstimator(), 0.1),
                        1
                    );
                }

                $raidData['battleResult'] = (object) $raidData['battleResult'];
            }

            $bosses[] = (object) $raidData;
        }

        $bosses = array_reverse($bosses, true);
        foreach ($bosses as $key => $boss) {
            $bosses[$key]->counter = array_reverse($boss->counter, true);
        }

        $svgWidth = $svgHeight = 0;
        ob_start();
        include __DIR__ . '/templates/raidlist.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return new RaidBossGraphic(
            trim($content ?: ''),
            $svgWidth,
            $svgHeight
        );
    }

    private function getName(RaidBoss $raidBoss, TranslationCollection $translationCollection): string
    {
        $pokemonName = PokemonNameRenderer::renderPokemonName($raidBoss->getPokemon(), $translationCollection);
        $temporary   = $raidBoss->getTemporaryEvolution();
        if ($temporary !== null) {
            $pokemonName = PokemonNameRenderer::renderPokemonMegaName(
                $raidBoss->getPokemon(),
                $temporary->getId(),
                $translationCollection
            );
        }

        return $pokemonName ?? '';
    }
}
