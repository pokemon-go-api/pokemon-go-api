<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

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
use function sprintf;
use function strtoupper;

final class RenderingRaidBoss
{
    private RaidBoss $raidBoss;
    private string $translatedName;
    private TypeEffectivenessCalculator $typeCalculator;
    private TypeWeatherCalculator $weatherCalculator;

    public function __construct(
        RaidBoss $raidBoss,
        string $translatedName,
        TypeEffectivenessCalculator $typeCalculator,
        TypeWeatherCalculator $weatherCalculator
    ) {
        $this->raidBoss          = $raidBoss;
        $this->translatedName    = $translatedName;
        $this->typeCalculator    = $typeCalculator;
        $this->weatherCalculator = $weatherCalculator;
    }

    public function getSplitBossName(int $splitByChars): SplitBossName
    {
        return new SplitBossName($this->translatedName, $splitByChars);
    }

    public function getId(): string
    {
        return $this->raidBoss->getPokemon()->getId();
    }

    public function getFormId(): string
    {
        return $this->raidBoss->getPokemonWithMegaFormId();
    }

    public function getLevel(): string
    {
        return $this->raidBoss->getRaidLevel();
    }

    public function getRaidBoss(): RaidBoss
    {
        return $this->raidBoss;
    }

    /**
     * @return array<int, WeatherBoost>
     */
    public function getWeatherBoost(): array
    {
        return $this->weatherCalculator->getWeatherBoost(...$this->getTypes());
    }

    public function getRaidBossStats(): PokemonStats
    {
        return $this->raidBoss->getPokemon()->getStats() ?? new PokemonStats(0, 0, 0);
    }

    /**
     * @return array<int, int>
     */
    public function getCpRange(): array
    {
        $raidBossStats = $this->getRaidBossStats();

        return [
            CpCalculator::calculateRaidMinCp($raidBossStats),
            CpCalculator::calculateRaidMaxCp($raidBossStats),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function getCpRangeWeatherBoosted(): array
    {
        $raidBossStats = $this->getRaidBossStats();

        return [
            CpCalculator::calculateRaidWeatherBoostMinCp($raidBossStats),
            CpCalculator::calculateRaidWeatherBoostMaxCp($raidBossStats),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getDifficulty(): array
    {
        $battleResults = $this->raidBoss->getBattleResults();
        $difficulty    = [];
        if (count($battleResults) > 0) {
            foreach ($battleResults as $battleResult) {
                $difficulty[$battleResult->getBattleConfiguration()->getName()] = number_format(
                    max($battleResult->getTotalEstimator(), 0.1),
                    1,
                    '.',
                    ''
                );
            }
        }

        return $difficulty;
    }

    /** @return array<int, PokemonType> */
    public function getTypes(): array
    {
        $raidBossPokemon    = $this->raidBoss->getPokemon();
        $temporaryEvolution = $this->raidBoss->getTemporaryEvolution();
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

        return $raidBossTypes;
    }

    /** @return array<int, string> */
    public function getTypeNames(): array
    {
        return array_map(
            static fn (PokemonType $pokemonType): string => $pokemonType->getType(),
            array_filter(
                $this->getTypes(),
                static fn (PokemonType $pokemonType): bool => $pokemonType->getType() !== PokemonType::NONE
            )
        );
    }

    public function getRaidBossEggUrl(): string
    {
        switch ($this->getLevel()) {
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

        return sprintf(
            //phpcs:ignore Generic.Files.LineLength.TooLong
            'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Raids/raid_egg_%s_icon_notification.png',
            $levelIcon
        );
    }

    public function getPokemonTypeImageUrl(string $type): string
    {
        return sprintf(
            'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Types/POKEMON_TYPE_%s.png',
            strtoupper($type)
        );
    }

    public function getWeatherIconUrl(WeatherBoost $weatherBoost): string
    {
        return sprintf(
            'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Weather/weatherIcon_small_%s.png',
            $weatherBoost->getAssetsName()
        );
    }

    /**
     * @return array<string, float>
     */
    public function getEffectiveCounterTypes(): array
    {
        return array_reverse($this->typeCalculator->getAllEffectiveTypes(...$this->getTypes()));
    }
}
