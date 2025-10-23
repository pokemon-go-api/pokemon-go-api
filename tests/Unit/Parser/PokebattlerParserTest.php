<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\CacheLoader;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\IO\RemoteFileLoader;
use PokemonGoApi\PogoAPI\Parser\PokebattlerParser;
use PokemonGoApi\PogoAPI\Types\BattleConfiguration;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidLevel;
use PokemonGoApi\PogoAPI\Types\TemporaryEvolution;
use Psr\Log\LoggerInterface;

use function sys_get_temp_dir;

#[CoversClass(PokebattlerParser::class)]
#[UsesClass(RaidBossCollection::class)]
#[UsesClass(JsonParser::class)]
#[UsesClass(BattleConfiguration::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(RaidBoss::class)]
class PokebattlerParserTest extends TestCase
{
    public function testBuildApiUrl(): void
    {
        $cacheLoaderMock = $this->getMockBuilder(CacheLoader::class)
            ->setConstructorArgs([
                $this->createMock(RemoteFileLoader::class),
                $this->createMock(DateTimeImmutable::class),
                sys_get_temp_dir(),
                $this->createMock(LoggerInterface::class),
            ])
            ->getMock();
        $matcher         = self::exactly(6);
        $cacheLoaderMock->expects(
            $matcher,
        )->method('fetchPokebattlerUrl')->willReturnCallback(static fn () => match ($matcher->numberOfInvocations()) {
            //phpcs:ignore Generic.Files.LineLength.TooLong
            1 => ['https://fight.pokebattler.com/raids/defenders/CHARIZARD_MEGA_X/levels/RAID_LEVEL_MEGA/attackers/levels/20/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_0'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            2 => ['https://fight.pokebattler.com/raids/defenders/CHARIZARD_MEGA_X/levels/RAID_LEVEL_MEGA/attackers/levels/30/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_3'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            3 => ['https://fight.pokebattler.com/raids/defenders/CHARIZARD_MEGA_X/levels/RAID_LEVEL_MEGA/attackers/levels/40/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_4'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            4 => ['https://fight.pokebattler.com/raids/defenders/DUMMY_FORM_ID/levels/RAID_LEVEL_5/attackers/levels/20/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_0'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            5 => ['https://fight.pokebattler.com/raids/defenders/DUMMY_FORM_ID/levels/RAID_LEVEL_5/attackers/levels/30/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_3'],
            //phpcs:ignore Generic.Files.LineLength.TooLong
            6 => ['https://fight.pokebattler.com/raids/defenders/DUMMY_FORM_ID/levels/RAID_LEVEL_5/attackers/levels/40/strategies/CINEMATIC_ATTACK_WHEN_POSSIBLE/DEFENSE_RANDOM_MC?sort=ESTIMATOR&weatherCondition=NO_WEATHER&dodgeStrategy=DODGE_REACTION_TIME&aggregation=AVERAGE&includeLegendary=true&includeShadow=false&includeMegas=false&attackerTypes=POKEMON_TYPE_ALL&friendLevel=FRIENDSHIP_LEVEL_4'],
            default => []
        })->willReturn(__DIR__ . '/Fixtures/emptyfile.json');

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
            RaidLevel::RaidMega,
            new TemporaryEvolution('CHARIZARD_MEGA_X', PokemonType::none(), PokemonType::none()),
            null,
        ));

        $raidBossCollection->add(new RaidBoss(
            new Pokemon(1, 'DUMMY', 'DUMMY_FORM_ID', PokemonType::none(), null),
            false,
            RaidLevel::Raid5,
            null,
            null,
        ));
        $sut->appendResults($raidBossCollection);
    }
}
