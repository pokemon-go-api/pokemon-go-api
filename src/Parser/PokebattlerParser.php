<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Parser;

use Exception;
use PokemonGoLingen\PogoAPI\CacheLoader;
use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Types\BattleConfiguration;
use PokemonGoLingen\PogoAPI\Types\BattleResult;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;

use function file_get_contents;
use function http_build_query;
use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class PokebattlerParser
{
    private CacheLoader $cacheLoader;
    /** @var BattleConfiguration[] */
    private array $battleConfigurations;

    public function __construct(
        CacheLoader $cacheLoader,
        BattleConfiguration ...$battleConfigurations
    ) {
        $this->cacheLoader          = $cacheLoader;
        $this->battleConfigurations = $battleConfigurations;
    }

    public function appendResults(RaidBossCollection $raidBossCollection): RaidBossCollection
    {
        $raidBossesWithDifficulty = clone $raidBossCollection;
        foreach ($raidBossCollection->toArray() as $raidBoss) {
            $battleResults = [];
            foreach ($this->battleConfigurations as $battleConfiguration) {
                $pokebattlerResultFile = $this->cacheLoader->fetchPokebattlerUrl(
                    $this->buildApiUrl($raidBoss, $battleConfiguration),
                    $this->buildCacheKey($raidBoss, $battleConfiguration)
                );

                $json = json_decode(
                    file_get_contents($pokebattlerResultFile) ?: 'false',
                    false,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (! isset($json->attackers)) {
                    continue;
                }

                $battleResults[] = new BattleResult(
                    $battleConfiguration,
                    $json->attackers[0]->randomMove->total->estimator
                );
            }

            $raidBossReference = $raidBossesWithDifficulty->get($raidBoss);
            if ($raidBossReference === null) {
                continue;
            }

            $raidBossReference->setBattleResults(...$battleResults);
        }

        return $raidBossCollection;
    }

    private function buildApiUrl(RaidBoss $raidBoss, BattleConfiguration $battleConfiguration): string
    {
        $url = sprintf(
            //phpcs:ignore Generic.Files.LineLength.TooLong
            'https://fight.pokebattler.com/raids/defenders/%s/levels/RAID_LEVEL_%s/attackers/levels/%d/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?',
            $this->raidBossName($raidBoss),
            $this->raidLevelConstantToLevelNumber($raidBoss->getRaidLevel()),
            $battleConfiguration->getPokemonLevel()
        );

        $url .= http_build_query([
            'sort'             => 'ESTIMATOR',
            'weatherCondition' => 'NO_WEATHER',
            'dodgeStrategy'    => 'DODGE_REACTION_TIME',
            'aggregation'      => 'AVERAGE',
            'includeLegendary' => 'true',
            'includeShadow'    => 'false',
            'includeMegas'     => 'false',
            'attackerTypes'    => 'POKEMON_TYPE_ALL',
            'friendLevel'      => 'FRIENDSHIP_LEVEL_' . $battleConfiguration->getFriendShipLevel(),
        ]);

        return $url;
    }

    private function raidLevelConstantToLevelNumber(string $raidLevel): string
    {
        switch ($raidLevel) {
            case RaidBoss::RAID_LEVEL_1:
                return '1';

            case RaidBoss::RAID_LEVEL_3:
                return '3';

            case RaidBoss::RAID_LEVEL_5:
                return '5';

            case RaidBoss::RAID_LEVEL_EX:
                return '6';

            case RaidBoss::RAID_LEVEL_MEGA:
                return 'MEGA';

            default:
                throw new Exception('Unknown RaidLevel', 1618743729200);
        }
    }

    private function raidBossName(RaidBoss $raidBoss): string
    {
        if ($raidBoss->getPokemon()->getPokemonForm() !== null) {
            return $raidBoss->getPokemonId() . '_FORM';
        }

        return $raidBoss->getPokemonId();
    }

    private function buildCacheKey(RaidBoss $raidBoss, BattleConfiguration $battleConfiguration): string
    {
        return $raidBoss->getPokemonId() . '_' . $battleConfiguration->getName();
    }
}
