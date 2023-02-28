<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\CacheLoader;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Parser\PokebattlerParser;
use PokemonGoApi\PogoAPI\Types\BattleConfiguration;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\TemporaryEvolution;

/**
 * @uses \PokemonGoApi\PogoAPI\Collections\RaidBossCollection
 * @uses \PokemonGoApi\PogoAPI\IO\JsonParser
 * @uses \PokemonGoApi\PogoAPI\Types\BattleConfiguration
 * @uses \PokemonGoApi\PogoAPI\Types\Pokemon
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonType
 * @uses \PokemonGoApi\PogoAPI\Types\RaidBoss
 *
 * @covers \PokemonGoApi\PogoAPI\Parser\PokebattlerParser
 */
class PokebattlerParserTest extends TestCase
{
    public function testBuildApiUrl(): void
    {
        $cacheLoaderMock = $this->createMock(CacheLoader::class);
        $cacheLoaderMock->expects(
            self::exactly(6),
        )->method('fetchPokebattlerUrl')
        ->withConsecutive(
            //phpcs:ignore Generic.Files.LineLength.TooLong
            ['https://fight.pokebattler.com/raids/defenders/CHARIZARD_MEGA_X/levels/RAID_LEVEL_MEGA/attackers/levels/20/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_0'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            ['https://fight.pokebattler.com/raids/defenders/CHARIZARD_MEGA_X/levels/RAID_LEVEL_MEGA/attackers/levels/30/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_3'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            ['https://fight.pokebattler.com/raids/defenders/CHARIZARD_MEGA_X/levels/RAID_LEVEL_MEGA/attackers/levels/40/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_4'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            ['https://fight.pokebattler.com/raids/defenders/DUMMY_FORM_ID/levels/RAID_LEVEL_5/attackers/levels/20/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_0'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            ['https://fight.pokebattler.com/raids/defenders/DUMMY_FORM_ID/levels/RAID_LEVEL_5/attackers/levels/30/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_3'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            ['https://fight.pokebattler.com/raids/defenders/DUMMY_FORM_ID/levels/RAID_LEVEL_5/attackers/levels/40/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_4'],
        )->willReturn(__DIR__ . '/Fixtures/emptyfile.json');

        $sut = new PokebattlerParser(
            $cacheLoaderMock,
            BattleConfiguration::easy(),
            BattleConfiguration::normal(),
            BattleConfiguration::hard(),
        );

        $raidBossCollection = new RaidBossCollection();

        $raidBossCollection->add(new RaidBoss(
            new Pokemon(2, 'MEGA_POKEMON', 'MEGA_POKEMON', PokemonType::none(), null),
            false,
            RaidBoss::RAID_LEVEL_MEGA,
            new TemporaryEvolution('CHARIZARD_MEGA_X', PokemonType::none(), PokemonType::none()),
            null,
        ));

        $raidBossCollection->add(new RaidBoss(
            new Pokemon(1, 'DUMMY', 'DUMMY_FORM_ID', PokemonType::none(), null),
            false,
            RaidBoss::RAID_LEVEL_5,
            null,
            null,
        ));
        $sut->appendResults($raidBossCollection);
    }
}
