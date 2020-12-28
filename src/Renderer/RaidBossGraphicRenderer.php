<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Renderer;

use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;
use PokemonGoLingen\PogoAPI\Types\PokemonStats;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;
use PokemonGoLingen\PogoAPI\Types\WeatherBoost;
use PokemonGoLingen\PogoAPI\Util\CpCalculator;
use PokemonGoLingen\PogoAPI\Util\TypeEffectivenessCalculator;
use PokemonGoLingen\PogoAPI\Util\TypeWeatherCalculator;

use function array_filter;
use function array_map;
use function array_reverse;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function sprintf;
use function str_replace;
use function trim;

final class RaidBossGraphicRenderer
{
    public function buildGraphic(
        RaidBossCollection $raidBossCollection,
        TranslationCollection $translationCollection
    ): string {
        $weatherCalculator = new TypeWeatherCalculator();
        $typeCalculator    = new TypeEffectivenessCalculator();
        $bosses            = [];
        foreach ($raidBossCollection->toArray() as $raidBoss) {
            $raidBossPokemon = $raidBoss->getPokemon();

            switch ($raidBoss->getRaidLevel()) {
                case RaidBoss::RAID_LEVEL_EX:
                    $levelIcon = 'EX';
                    break;
                case RaidBoss::RAID_LEVEL_MEGA:
                    $levelIcon = 'M';
                    break;
                case RaidBoss::RAID_LEVEL_5:
                    $levelIcon = 'V';
                    break;
                case RaidBoss::RAID_LEVEL_3:
                    $levelIcon = 'III';
                    break;
                case RaidBoss::RAID_LEVEL_1:
                    $levelIcon = 'I';
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

            $bosses[] = (object) [
                'id'           => $raidBoss->getPokemonId(),
                'level'        => $raidBoss->getRaidLevel(),
                'levelIcon'    => $levelIcon,
                'name'         => $this->getName($raidBoss, $translationCollection),
                'shiny'        => $raidBoss->isShinyAvailable(),
                'image'        => sprintf('pokemon_icon_%03d_%02d', $raidBossPokemon->getDexNr(), $assetsBundleId),
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
        }

        $bosses = array_reverse($bosses, true);
        foreach ($bosses as $key => $boss) {
            $bosses[$key]->counter = array_reverse($boss->counter, true);
        }

        ob_start();
        include __DIR__ . '/templates/raidlist.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return trim($content ?: '');
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

        return str_replace(
            ['Mega-', 'Alola-', 'Galar-', 'Mega ', 'Alolan ', 'Galarian '],
            ['M-', 'A-', 'G-', 'M ', 'A ', 'G '],
            $pokemonName ?? ''
        );
    }
}
