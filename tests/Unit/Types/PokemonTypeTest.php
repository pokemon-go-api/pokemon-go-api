<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonType;

/** @covers \PokemonGoApi\PogoAPI\Types\PokemonType */
class PokemonTypeTest extends TestCase
{
    public function testDark(): void
    {
        $sut = PokemonType::dark();
        self::assertSame('Dark', $sut->getType());
        self::assertSame('POKEMON_TYPE_DARK', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(2, $sut->getHalfDamageFrom());
        self::assertCount(1, $sut->getNoDamageFrom());
    }

    public function testBug(): void
    {
        $sut = PokemonType::bug();
        self::assertSame('Bug', $sut->getType());
        self::assertSame('POKEMON_TYPE_BUG', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(3, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testFire(): void
    {
        $sut = PokemonType::fire();
        self::assertSame('Fire', $sut->getType());
        self::assertSame('POKEMON_TYPE_FIRE', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(6, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testGhost(): void
    {
        $sut = PokemonType::Ghost();
        self::assertSame('Ghost', $sut->getType());
        self::assertSame('POKEMON_TYPE_GHOST', $sut->getGameMasterTypeName());
        self::assertCount(2, $sut->getDoubleDamageFrom());
        self::assertCount(2, $sut->getHalfDamageFrom());
        self::assertCount(2, $sut->getNoDamageFrom());
    }

    public function testSteel(): void
    {
        $sut = PokemonType::Steel();
        self::assertSame('Steel', $sut->getType());
        self::assertSame('POKEMON_TYPE_STEEL', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(10, $sut->getHalfDamageFrom());
        self::assertCount(1, $sut->getNoDamageFrom());
    }

    public function testElectric(): void
    {
        $sut = PokemonType::Electric();
        self::assertSame('Electric', $sut->getType());
        self::assertSame('POKEMON_TYPE_ELECTRIC', $sut->getGameMasterTypeName());
        self::assertCount(1, $sut->getDoubleDamageFrom());
        self::assertCount(3, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testPoison(): void
    {
        $sut = PokemonType::Poison();
        self::assertSame('Poison', $sut->getType());
        self::assertSame('POKEMON_TYPE_POISON', $sut->getGameMasterTypeName());
        self::assertCount(2, $sut->getDoubleDamageFrom());
        self::assertCount(5, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testGround(): void
    {
        $sut = PokemonType::Ground();
        self::assertSame('Ground', $sut->getType());
        self::assertSame('POKEMON_TYPE_GROUND', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(2, $sut->getHalfDamageFrom());
        self::assertCount(1, $sut->getNoDamageFrom());
    }

    public function testIce(): void
    {
        $sut = PokemonType::ice();
        self::assertSame('Ice', $sut->getType());
        self::assertSame('POKEMON_TYPE_ICE', $sut->getGameMasterTypeName());
        self::assertCount(4, $sut->getDoubleDamageFrom());
        self::assertCount(1, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testPsychic(): void
    {
        $sut = PokemonType::Psychic();
        self::assertSame('Psychic', $sut->getType());
        self::assertSame('POKEMON_TYPE_PSYCHIC', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(2, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testDragon(): void
    {
        $sut = PokemonType::Dragon();
        self::assertSame('Dragon', $sut->getType());
        self::assertSame('POKEMON_TYPE_DRAGON', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(4, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testWater(): void
    {
        $sut = PokemonType::Water();
        self::assertSame('Water', $sut->getType());
        self::assertSame('POKEMON_TYPE_WATER', $sut->getGameMasterTypeName());
        self::assertCount(2, $sut->getDoubleDamageFrom());
        self::assertCount(4, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testRock(): void
    {
        $sut = PokemonType::rock();
        self::assertSame('Rock', $sut->getType());
        self::assertSame('POKEMON_TYPE_ROCK', $sut->getGameMasterTypeName());
        self::assertCount(5, $sut->getDoubleDamageFrom());
        self::assertCount(4, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testNone(): void
    {
        $sut = PokemonType::none();
        self::assertSame('None', $sut->getType());
        self::assertSame('POKEMON_TYPE_NONE', $sut->getGameMasterTypeName());
        self::assertCount(0, $sut->getDoubleDamageFrom());
        self::assertCount(0, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testFairy(): void
    {
        $sut = PokemonType::Fairy();
        self::assertSame('Fairy', $sut->getType());
        self::assertSame('POKEMON_TYPE_FAIRY', $sut->getGameMasterTypeName());
        self::assertCount(2, $sut->getDoubleDamageFrom());
        self::assertCount(3, $sut->getHalfDamageFrom());
        self::assertCount(1, $sut->getNoDamageFrom());
    }

    public function testFlying(): void
    {
        $sut = PokemonType::Flying();
        self::assertSame('Flying', $sut->getType());
        self::assertSame('POKEMON_TYPE_FLYING', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(3, $sut->getHalfDamageFrom());
        self::assertCount(1, $sut->getNoDamageFrom());
    }

    public function testGrass(): void
    {
        $sut = PokemonType::Grass();
        self::assertSame('Grass', $sut->getType());
        self::assertSame('POKEMON_TYPE_GRASS', $sut->getGameMasterTypeName());
        self::assertCount(5, $sut->getDoubleDamageFrom());
        self::assertCount(4, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }

    public function testNormal(): void
    {
        $sut = PokemonType::Normal();
        self::assertSame('Normal', $sut->getType());
        self::assertSame('POKEMON_TYPE_NORMAL', $sut->getGameMasterTypeName());
        self::assertCount(1, $sut->getDoubleDamageFrom());
        self::assertCount(0, $sut->getHalfDamageFrom());
        self::assertCount(1, $sut->getNoDamageFrom());
    }

    public function testFighting(): void
    {
        $sut = PokemonType::Fighting();
        self::assertSame('Fighting', $sut->getType());
        self::assertSame('POKEMON_TYPE_FIGHTING', $sut->getGameMasterTypeName());
        self::assertCount(3, $sut->getDoubleDamageFrom());
        self::assertCount(3, $sut->getHalfDamageFrom());
        self::assertCount(0, $sut->getNoDamageFrom());
    }
}
