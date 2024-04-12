<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\WeatherBoost;

#[CoversClass(WeatherBoost::class)]
#[UsesClass(PokemonType::class)]
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
        $this->assertEquals($snow, WeatherBoost::snow());
    }

    public function testPartlyCloudy(): void
    {
        $sut = WeatherBoost::partlyCloudy();
        $this->assertSame('partlyCloudy', $sut->getWeather());
        $this->assertSame('partlycloudy_day', $sut->getAssetsName());
        $this->assertCount(2, $sut->getBoostedTypes());
    }

    public function testWindy(): void
    {
        $sut = WeatherBoost::windy();
        $this->assertSame('windy', $sut->getWeather());
        $this->assertSame('windy', $sut->getAssetsName());
        $this->assertCount(3, $sut->getBoostedTypes());
    }

    public function testFog(): void
    {
        $sut = WeatherBoost::fog();
        $this->assertSame('fog', $sut->getWeather());
        $this->assertSame('fog', $sut->getAssetsName());
        $this->assertCount(2, $sut->getBoostedTypes());
    }

    public function testSnow(): void
    {
        $sut = WeatherBoost::snow();
        $this->assertSame('snow', $sut->getWeather());
        $this->assertSame('snow', $sut->getAssetsName());
        $this->assertCount(2, $sut->getBoostedTypes());
    }

    public function testRain(): void
    {
        $sut = WeatherBoost::rain();
        $this->assertSame('rain', $sut->getWeather());
        $this->assertSame('rain', $sut->getAssetsName());
        $this->assertCount(3, $sut->getBoostedTypes());
    }

    public function testCloudy(): void
    {
        $sut = WeatherBoost::cloudy();
        $this->assertSame('cloudy', $sut->getWeather());
        $this->assertSame('cloudy', $sut->getAssetsName());
        $this->assertCount(3, $sut->getBoostedTypes());
    }

    public function testSunny(): void
    {
        $sut = WeatherBoost::sunny();
        $this->assertSame('sunny', $sut->getWeather());
        $this->assertSame('sunny', $sut->getAssetsName());
        $this->assertCount(3, $sut->getBoostedTypes());
    }

    public function testExtreme(): void
    {
        $sut = WeatherBoost::extreme();
        $this->assertSame('extreme', $sut->getWeather());
        $this->assertSame('extreme', $sut->getAssetsName());
        $this->assertCount(0, $sut->getBoostedTypes());
    }
}
