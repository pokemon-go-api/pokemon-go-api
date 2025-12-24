<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonStats;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolution;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidLevel;
use PokemonGoApi\PogoAPI\Types\WeatherBoost;
use PokemonGoApi\PogoAPI\Util\CpCalculator;
use PokemonGoApi\PogoAPI\Util\TypeEffectivenessCalculator;
use PokemonGoApi\PogoAPI\Util\TypeWeatherCalculator;

use function array_filter;
use function array_map;
use function array_reverse;
use function max;
use function number_format;
use function sprintf;
use function strtoupper;

final readonly class RenderingRaidBoss
{
    public function __construct(
        private RaidBoss $raidBoss,
        private string $translatedName,
        private TypeEffectivenessCalculator $typeCalculator,
        private TypeWeatherCalculator $weatherCalculator,
    ) {
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

    public function getLevel(): RaidLevel
    {
        return $this->raidBoss->getRaidLevel();
    }

    public function getRaidBoss(): RaidBoss
    {
        return $this->raidBoss;
    }

    /** @return array<int, WeatherBoost> */
    public function getWeatherBoost(): array
    {
        return $this->weatherCalculator->getWeatherBoost(...$this->getTypes());
    }

    public function getRaidBossStats(): PokemonStats
    {
        return $this->raidBoss->getPokemon()->getStats();
    }

    /** @return array<int, int> */
    public function getCpRange(): array
    {
        $raidBossStats = $this->getRaidBossStats();

        return [
            CpCalculator::calculateRaidMinCp($raidBossStats),
            CpCalculator::calculateRaidMaxCp($raidBossStats),
        ];
    }

    /** @return array<int, int> */
    public function getCpRangeWeatherBoosted(): array
    {
        $raidBossStats = $this->getRaidBossStats();

        return [
            CpCalculator::calculateRaidWeatherBoostMinCp($raidBossStats),
            CpCalculator::calculateRaidWeatherBoostMaxCp($raidBossStats),
        ];
    }

    /** @return array<string, string> */
    public function getDifficulty(): array
    {
        $battleResults = $this->raidBoss->getBattleResults();
        $difficulty    = [];
        foreach ($battleResults as $battleResult) {
            $difficulty[$battleResult->getBattleConfiguration()->getName()] = number_format(
                max($battleResult->getTotalEstimator(), 0.1),
                1,
                '.',
                '',
            );
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
        if ($temporaryEvolution instanceof TemporaryEvolution) {
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
                static fn (PokemonType $pokemonType): bool => $pokemonType->getType() !== PokemonType::NONE,
            ),
        );
    }

    public function getRaidBossEggUrl(): string
    {
        $levelIcon = $this->getLevel()->getEggIconName();

        return sprintf(
            //phpcs:ignore Generic.Files.LineLength.TooLong
            'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Raids/raid_%s_icon_notification.png',
            $levelIcon,
        );
    }

    public function getPokemonTypeImageUrl(string $type): string
    {
        return sprintf(
            'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Types/POKEMON_TYPE_%s.png',
            strtoupper($type),
        );
    }

    public function getWeatherIconUrl(WeatherBoost $weatherBoost): string
    {
        return sprintf(
            'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Weather/weatherIcon_small_%s.png',
            $weatherBoost->getAssetsName(),
        );
    }

    /** @return array<string, float> */
    public function getEffectiveCounterTypes(): array
    {
        return array_reverse($this->typeCalculator->getAllEffectiveTypes(...$this->getTypes()));
    }
}
