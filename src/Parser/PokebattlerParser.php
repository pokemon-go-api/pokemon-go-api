<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use PokemonGoApi\PogoAPI\CacheLoader;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Types\BattleConfiguration;
use PokemonGoApi\PogoAPI\Types\BattleResult;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use Throwable;

use function file_get_contents;
use function http_build_query;
use function sprintf;

class PokebattlerParser
{
    /** @var BattleConfiguration[] */
    private readonly array $battleConfigurations;

    public function __construct(
        private readonly CacheLoader $cacheLoader,
        BattleConfiguration ...$battleConfigurations,
    ) {
        $this->battleConfigurations = $battleConfigurations;
    }

    public function appendResults(RaidBossCollection $raidBossCollection): RaidBossCollection
    {
        $raidBossesWithDifficulty = clone $raidBossCollection;
        foreach ($raidBossCollection->toArray() as $raidBoss) {
            $battleResults = [];
            foreach ($this->battleConfigurations as $battleConfiguration) {
                $pokebattlerResultFile = null;
                try {
                    $pokebattlerResultFile = $this->cacheLoader->fetchPokebattlerUrl(
                        $this->buildApiUrl($raidBoss, $battleConfiguration),
                        $this->buildCacheKey($raidBoss, $battleConfiguration),
                    );
                } catch (Throwable) {
                }

                if ($pokebattlerResultFile === null) {
                    try {
                        $pokebattlerResultFile = $this->cacheLoader->fetchPokebattlerUrl(
                            $this->buildApiUrl($raidBoss, $battleConfiguration, true),
                            $this->buildCacheKey($raidBoss, $battleConfiguration),
                        );
                    } catch (Throwable) {
                    }
                }

                if ($pokebattlerResultFile === null) {
                    continue;
                }

                $json = JsonParser::decodeToObject(file_get_contents($pokebattlerResultFile) ?: '[]');

                if (! isset($json->attackers)) {
                    continue;
                }

                $battleResults[] = new BattleResult(
                    $battleConfiguration,
                    $json->attackers[0]->randomMove->total->estimator,
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

    private function buildApiUrl(
        RaidBoss $raidBoss,
        BattleConfiguration $battleConfiguration,
        bool $addForm = false,
    ): string {
        $url = sprintf(
            //phpcs:ignore Generic.Files.LineLength.TooLong
            'https://fight.pokebattler.com/raids/defenders/%s/levels/RAID_LEVEL_%s/attackers/levels/%d/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?',
            $this->raidBossName($raidBoss, $addForm, $raidBoss->getRaidLevel()->isShadow()),
            $raidBoss->getRaidLevel()->toPokebattlerLevel(),
            $battleConfiguration->getPokemonLevel(),
        );

        return $url . http_build_query([
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
    }

    private function raidBossName(RaidBoss $raidBoss, bool $addForm, bool $isShadow): string
    {
        $suffix = $isShadow ? '_SHADOW_FORM' : '';
        if ($addForm && $raidBoss->getPokemon()->getPokemonForm() !== null) {
            return $raidBoss->getPokemonWithMegaFormId() . '_FORM';
        }

        if ($addForm) {
            return $raidBoss->getPokemon()->getId() . $suffix;
        }

        return $raidBoss->getPokemonWithMegaFormId() . $suffix;
    }

    private function buildCacheKey(RaidBoss $raidBoss, BattleConfiguration $battleConfiguration): string
    {
        return $raidBoss->getPokemonWithMegaFormId() . '_' . $battleConfiguration->getName();
    }
}
