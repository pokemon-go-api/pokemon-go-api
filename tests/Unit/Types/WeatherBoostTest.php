<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\WeatherBoost;

/**
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonType
 *
 * @covers \PokemonGoApi\PogoAPI\Types\WeatherBoost
 */
class WeatherBoostTest extends TestCase
{
    public function testCreateFromTypeWithException(): void
    {
        self::expectExceptionCode(1609070869725);
        WeatherBoost::createFromType('non existing');
    }

    public function testCreateFromType(): void
    {
        $snow = WeatherBoost::createFromType('snow');
        self::assertEquals($snow, WeatherBoost::snow());
    }

    public function testPartlyCloudy(): void
    {
        $sut = WeatherBoost::partlyCloudy();
        self::assertSame('partlyCloudy', $sut->getWeather());
        self::assertSame('partlycloudy_day', $sut->getAssetsName());
        self::assertCount(2, $sut->getBoostedTypes());
    }

    public function testWindy(): void
    {
        $sut = WeatherBoost::windy();
        self::assertSame('windy', $sut->getWeather());
        self::assertSame('windy', $sut->getAssetsName());
        self::assertCount(3, $sut->getBoostedTypes());
    }

    public function testFog(): void
    {
        $sut = WeatherBoost::fog();
        self::assertSame('fog', $sut->getWeather());
        self::assertSame('fog', $sut->getAssetsName());
        self::assertCount(2, $sut->getBoostedTypes());
    }

    public function testSnow(): void
    {
        $sut = WeatherBoost::snow();
        self::assertSame('snow', $sut->getWeather());
        self::assertSame('snow', $sut->getAssetsName());
        self::assertCount(2, $sut->getBoostedTypes());
    }

    public function testRain(): void
    {
        $sut = WeatherBoost::rain();
        self::assertSame('rain', $sut->getWeather());
        self::assertSame('rain', $sut->getAssetsName());
        self::assertCount(3, $sut->getBoostedTypes());
    }

    public function testCloudy(): void
    {
        $sut = WeatherBoost::cloudy();
        self::assertSame('cloudy', $sut->getWeather());
        self::assertSame('cloudy', $sut->getAssetsName());
        self::assertCount(3, $sut->getBoostedTypes());
    }

    public function testSunny(): void
    {
        $sut = WeatherBoost::sunny();
        self::assertSame('sunny', $sut->getWeather());
        self::assertSame('sunny', $sut->getAssetsName());
        self::assertCount(3, $sut->getBoostedTypes());
    }

    public function testExtreme(): void
    {
        $sut = WeatherBoost::extreme();
        self::assertSame('extreme', $sut->getWeather());
        self::assertSame('extreme', $sut->getAssetsName());
        self::assertCount(0, $sut->getBoostedTypes());
    }
}
