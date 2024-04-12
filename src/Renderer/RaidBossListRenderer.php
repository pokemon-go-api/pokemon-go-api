<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer;

use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoApi\PogoAPI\Types\PokemonStats;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\WeatherBoost;
use PokemonGoApi\PogoAPI\Util\CpCalculator;
use PokemonGoApi\PogoAPI\Util\TypeEffectivenessCalculator;
use PokemonGoApi\PogoAPI\Util\TypeWeatherCalculator;

use function array_filter;
use function array_map;
use function count;

final class RaidBossListRenderer
{
    /** @return array<string, array<int, mixed[]>> */
    public function buildList(
        RaidBossCollection $raidBossCollection,
        TranslationCollectionCollection $translationCollection,
    ): array {
        $weatherCalculator = new TypeWeatherCalculator();
        $typeCalculator    = new TypeEffectivenessCalculator();
        $bosses            = [];
        foreach ($raidBossCollection->toArray() as $raidBoss) {
            $raidBossPokemon = $raidBoss->getPokemon();

            $temporaryEvolution = $raidBoss->getTemporaryEvolution();
            $raidBossTypes      = [
                $raidBossPokemon->getTypePrimary(),
                $raidBossPokemon->getTypeSecondary(),
            ];
            if ($temporaryEvolution !== null) {
                $raidBossTypes = [
                    $temporaryEvolution->getTypePrimary(),
                    $temporaryEvolution->getTypeSecondary(),
                ];
            }

            $raidBossTypeNames = array_map(
                static fn (PokemonType $pokemonType): string => $pokemonType->getType(),
                array_filter(
                    $raidBossTypes,
                    static fn (PokemonType $pokemonType): bool => $pokemonType->getType() !== PokemonType::NONE,
                ),
            );

            $raidBossStats = $raidBossPokemon->getStats() ?: new PokemonStats(0, 0, 0);
            $pokemonImage  = $raidBoss->getPokemonImage();
            $raidData      = [
                'id'           => $raidBoss->getPokemon()->getId(),
                'form'         => $raidBoss->getPokemonWithMegaFormId(),
                'assets'        => $pokemonImage ? [
                    'image' => $pokemonImage->buildUrl(false),
                    'shinyImage' => $pokemonImage->buildUrl(true),
                ] : null,
                'level'        => $raidBoss->getRaidLevel(),
                'names'        => $this->getNames($raidBoss, $translationCollection),
                'shiny'        => $raidBoss->isShinyAvailable(),
                'types'        => $raidBossTypeNames,
                'counter'      => $typeCalculator->getAllEffectiveTypes(...$raidBossTypes),
                'weather'      => array_map(
                    static fn (WeatherBoost $weatherBoost): string => $weatherBoost->getAssetsName(),
                    $weatherCalculator->getWeatherBoost(...$raidBossTypes),
                ),
                'cpRange'      => [
                    CpCalculator::calculateRaidMinCp($raidBossStats),
                    CpCalculator::calculateRaidMaxCp($raidBossStats),
                ],
                'cpRangeBoost' => [
                    CpCalculator::calculateRaidWeatherBoostMinCp($raidBossStats),
                    CpCalculator::calculateRaidWeatherBoostMaxCp($raidBossStats),
                ],
                'battleResult' => null,
            ];

            $battleResults = $raidBoss->getBattleResults();
            if (count($battleResults) > 0) {
                foreach ($battleResults as $battleResult) {
                    $config                                       = $battleResult->getBattleConfiguration();
                    $raidData['battleResult'][$config->getName()] = [
                        'name'            => $config->getName(),
                        'friendshipLevel' => $config->getFriendShipLevel(),
                        'pokemonLevel'    => $config->getPokemonLevel(),
                        'totalEstimator'  => $battleResult->getTotalEstimator(),
                    ];
                }
            }

            $bosses[$raidBoss->getRaidLevel()][] = $raidData;
        }

        return $bosses;
    }

    /** @return array<string, string> */
    private function getNames(
        RaidBoss $raidBoss,
        TranslationCollectionCollection $translationCollectionCollection,
    ): array {
        $out = [];
        foreach ($translationCollectionCollection->getCollections() as $translationName => $translationCollection) {
            $pokemonName = PokemonNameRenderer::renderPokemonName($raidBoss->getPokemon(), $translationCollection);
            $temporary   = $raidBoss->getTemporaryEvolution();
            if ($temporary !== null) {
                $pokemonName = PokemonNameRenderer::renderPokemonMegaName(
                    $raidBoss->getPokemon(),
                    $temporary->getId(),
                    $translationCollection,
                );
            }

            $out[$translationName] = $pokemonName ?? '';
        }

        return $out;
    }
}
