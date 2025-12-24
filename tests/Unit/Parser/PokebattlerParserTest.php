<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\CacheLoader;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\IO\GithubLoader;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\IO\RemoteFileLoader;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Pokemon;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonStats;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolution;
use PokemonGoApi\PogoAPI\Parser\PokebattlerParser;
use PokemonGoApi\PogoAPI\Types\BattleConfiguration;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidLevel;
use Psr\Log\LoggerInterface;

use function sys_get_temp_dir;

#[CoversClass(PokebattlerParser::class)]
#[UsesClass(RaidBossCollection::class)]
#[UsesClass(JsonParser::class)]
#[UsesClass(BattleConfiguration::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(RaidBoss::class)]
final class PokebattlerParserTest extends TestCase
{
    public function testBuildApiUrl(): void
    {
        $cacheLoaderMock = $this->getMockBuilder(CacheLoader::class)
            ->setConstructorArgs([
                $this->createStub(RemoteFileLoader::class),
                $this->createStub(GithubLoader::class),
                $this->createStub(DateTimeImmutable::class),
                sys_get_temp_dir(),
                $this->createStub(LoggerInterface::class),
            ])
            ->getMock();
        $matcher         = $this->exactly(6);
        $cacheLoaderMock->expects($matcher)
            ->method('fetchPokebattlerUrl')
            ->willReturnCallback(static fn (): array => match ($matcher->numberOfInvocations()) {
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
            })->willReturn(__DIR__ . '/Fixtures/pokebattler.json');

        $sut = new PokebattlerParser(
            $cacheLoaderMock,
            BattleConfiguration::easy(),
            BattleConfiguration::normal(),
            BattleConfiguration::hard(),
        );

        $raidBossCollection = new RaidBossCollection();

        $raidBossCollection->add(new RaidBoss(
            new Pokemon(2, 'MEGA_POKEMON', 'MEGA_POKEMON', PokemonType::none(), PokemonType::none()),
            false,
            RaidLevel::RaidMega,
            new TemporaryEvolution('CHARIZARD_MEGA_X', new PokemonStats(0, 0, 0), PokemonType::none(), PokemonType::none()),
        ));

        $raidBossCollection->add(new RaidBoss(
            new Pokemon(1, 'DUMMY', 'DUMMY_FORM_ID', PokemonType::none(), PokemonType::none()),
            false,
            RaidLevel::Raid5,
            null,
        ));
        $sut->appendResults($raidBossCollection);
    }
}
