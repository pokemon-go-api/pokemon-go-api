<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Util;

use Generator;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Util\TypeWeatherCalculator;

use function count;

/**
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonType
 * @uses \PokemonGoApi\PogoAPI\Types\WeatherBoost
 *
 * @covers \PokemonGoApi\PogoAPI\Util\TypeWeatherCalculator
 */
class TypeWeatherCalculatorTest extends TestCase
{
    /**
     * @param string[] $expectedWeather
     *
     * @dataProvider weatherTypesDataProvider
     */
    public function testGetWeatherBoost(PokemonType $typeA, PokemonType $typeB, array $expectedWeather): void
    {
        $sut               = new TypeWeatherCalculator();
        $calculatedWeather = $sut->getWeatherBoost($typeA, $typeB);

        self::assertCount(count($expectedWeather), $calculatedWeather);
        foreach ($expectedWeather as $index => $weather) {
            self::assertSame($weather, $calculatedWeather[$index]->getWeather());
        }
    }

    /**
     * @return Generator<array<int, array<int, string>|PokemonType>>
     */
    public function weatherTypesDataProvider(): Generator
    {
        yield [PokemonType::ice(), PokemonType::steel(), ['snow']];
        yield [PokemonType::ice(), PokemonType::none(), ['snow']];
        yield [PokemonType::fire(), PokemonType::fighting(), ['sunny', 'cloudy']];
    }
}
