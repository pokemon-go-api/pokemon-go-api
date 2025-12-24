<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use PokemonGoApi\PogoAPI\CacheLoader;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Types\BattleConfiguration;
use PokemonGoApi\PogoAPI\Types\BattleResult;
use PokemonGoApi\PogoAPI\Types\PokeBattlerResult;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use Throwable;

use function file_get_contents;

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
                        $raidBoss,
                        $battleConfiguration,
                    );
                } catch (Throwable) {
                }

                if ($pokebattlerResultFile === null) {
                    continue;
                }

                try {
                    $resultPayload = JsonParser::decodeToFullArray(file_get_contents($pokebattlerResultFile) ?: '[]');
                    $result        = JsonMapper::map(PokeBattlerResult::class, $resultPayload);
                } catch (Throwable) {
                    continue;
                }

                $battleResults[] = new BattleResult(
                    $battleConfiguration,
                    $result->estimator,
                );
            }

            $raidBossReference = $raidBossesWithDifficulty->get($raidBoss);
            if (! $raidBossReference instanceof RaidBoss) {
                continue;
            }

            $raidBossReference->setBattleResults(...$battleResults);
        }

        return $raidBossCollection;
    }
}
