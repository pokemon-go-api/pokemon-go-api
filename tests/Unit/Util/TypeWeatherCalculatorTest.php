<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Util;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\WeatherBoost;
use PokemonGoApi\PogoAPI\Util\TypeWeatherCalculator;

use function count;

#[CoversClass(TypeWeatherCalculator::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(WeatherBoost::class)]
final class TypeWeatherCalculatorTest extends TestCase
{
    /** @param string[] $expectedWeather */
    #[DataProvider('weatherTypesDataProvider')]
    public function testGetWeatherBoost(PokemonType $typeA, PokemonType $typeB, array $expectedWeather): void
    {
        $sut               = new TypeWeatherCalculator();
        $calculatedWeather = $sut->getWeatherBoost($typeA, $typeB);

        $this->assertCount(count($expectedWeather), $calculatedWeather);
        foreach ($expectedWeather as $index => $weather) {
            $this->assertSame($weather, $calculatedWeather[$index]->getWeather());
        }
    }

    /** @return Generator<array<int, array<int, string>|PokemonType>> */
    public static function weatherTypesDataProvider(): Generator
    {
        yield [PokemonType::ice(), PokemonType::steel(), ['snow']];
        yield [PokemonType::ice(), PokemonType::none(), ['snow']];
        yield [PokemonType::fire(), PokemonType::fighting(), ['sunny', 'cloudy']];
    }
}
