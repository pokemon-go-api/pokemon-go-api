<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Renderer;

use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoLingen\PogoAPI\Types\PokemonStats;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;
use PokemonGoLingen\PogoAPI\Types\WeatherBoost;
use PokemonGoLingen\PogoAPI\Util\CpCalculator;
use PokemonGoLingen\PogoAPI\Util\TypeEffectivenessCalculator;
use PokemonGoLingen\PogoAPI\Util\TypeWeatherCalculator;

use function array_filter;
use function array_map;
use function sprintf;

final class RaidBossListRenderer
{
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%03d_%02d.png';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_SHINY_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%03d_%02d_shiny.png';

    /**
     * @return array<string, array<int, mixed[]>>
     */
    public function buildList(
        RaidBossCollection $raidBossCollection,
        TranslationCollectionCollection $translationCollection
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
                    static fn (PokemonType $pokemonType): bool => $pokemonType->getType() !== PokemonType::NONE
                )
            );

            $raidBossStats = $raidBossPokemon->getStats() ?: new PokemonStats(0, 0, 0);

            $bosses[$raidBoss->getRaidLevel()][] = [
                'id'           => $raidBoss->getPokemon()->getId(),
                'form'         => $temporaryEvolution ? $temporaryEvolution->getId() : $raidBoss->getPokemonId(),
                'assets'        => [
                    'image' => sprintf(
                        self::ASSETS_BASE_URL,
                        $raidBossPokemon->getDexNr(),
                        $temporaryEvolution
                            ? $temporaryEvolution->getAssetsBundleId()
                            : $raidBossPokemon->getAssetsBundleId()
                    ),
                    'shinyImage' => sprintf(
                        self::ASSETS_BASE_SHINY_URL,
                        $raidBossPokemon->getDexNr(),
                        $temporaryEvolution
                            ? $temporaryEvolution->getAssetsBundleId()
                            : $raidBossPokemon->getAssetsBundleId()
                    ),
                ],
                'level'        => $raidBoss->getRaidLevel(),
                'names'        => $this->getName($raidBoss, $translationCollection),
                'shiny'        => $raidBoss->isShinyAvailable(),
                'types'        => $raidBossTypeNames,
                'counter'      => $typeCalculator->getAllEffectiveTypes(...$raidBossTypes),
                'weather'      => array_map(
                    static fn (WeatherBoost $weatherBoost): string => $weatherBoost->getAssetsName(),
                    $weatherCalculator->getWeatherBoost(...$raidBossTypes)
                ),
                'cpRange'      => [
                    CpCalculator::calculateRaidMinCp($raidBossStats),
                    CpCalculator::calculateRaidMaxCp($raidBossStats),
                ],
                'cpRangeBoost' => [
                    CpCalculator::calculateRaidWeatherBoostMinCp($raidBossStats),
                    CpCalculator::calculateRaidWeatherBoostMaxCp($raidBossStats),
                ],
            ];
        }

        return $bosses;
    }

    /**
     * @return array<string, string>
     */
    private function getName(
        RaidBoss $raidBoss,
        TranslationCollectionCollection $translationCollectionCollection
    ): array {
        $out = [];
        foreach ($translationCollectionCollection->getCollections() as $translationName => $translationCollection) {
            $pokemonName = PokemonNameRenderer::renderPokemonName($raidBoss->getPokemon(), $translationCollection);
            $temporary   = $raidBoss->getTemporaryEvolution();
            if ($temporary !== null) {
                $pokemonName = PokemonNameRenderer::renderPokemonMegaName(
                    $raidBoss->getPokemon(),
                    $temporary->getId(),
                    $translationCollection
                );
            }

            if ($pokemonName === null) {
                continue;
            }

            $out[$translationName] = $pokemonName;
        }

        return $out;
    }
}
