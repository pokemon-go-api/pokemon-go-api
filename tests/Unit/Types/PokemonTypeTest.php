<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonType;

#[CoversClass(PokemonType::class)]
final class PokemonTypeTest extends TestCase
{
    public function testDark(): void
    {
        $sut = PokemonType::dark();
        $this->assertSame('Dark', $sut->getType());
        $this->assertSame('POKEMON_TYPE_DARK', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(2, $sut->getHalfDamageFrom());
        $this->assertCount(1, $sut->getNoDamageFrom());
    }

    public function testBug(): void
    {
        $sut = PokemonType::bug();
        $this->assertSame('Bug', $sut->getType());
        $this->assertSame('POKEMON_TYPE_BUG', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(3, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testFire(): void
    {
        $sut = PokemonType::fire();
        $this->assertSame('Fire', $sut->getType());
        $this->assertSame('POKEMON_TYPE_FIRE', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(6, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testGhost(): void
    {
        $sut = PokemonType::Ghost();
        $this->assertSame('Ghost', $sut->getType());
        $this->assertSame('POKEMON_TYPE_GHOST', $sut->getGameMasterTypeName());
        $this->assertCount(2, $sut->getDoubleDamageFrom());
        $this->assertCount(2, $sut->getHalfDamageFrom());
        $this->assertCount(2, $sut->getNoDamageFrom());
    }

    public function testSteel(): void
    {
        $sut = PokemonType::Steel();
        $this->assertSame('Steel', $sut->getType());
        $this->assertSame('POKEMON_TYPE_STEEL', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(10, $sut->getHalfDamageFrom());
        $this->assertCount(1, $sut->getNoDamageFrom());
    }

    public function testElectric(): void
    {
        $sut = PokemonType::Electric();
        $this->assertSame('Electric', $sut->getType());
        $this->assertSame('POKEMON_TYPE_ELECTRIC', $sut->getGameMasterTypeName());
        $this->assertCount(1, $sut->getDoubleDamageFrom());
        $this->assertCount(3, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testPoison(): void
    {
        $sut = PokemonType::Poison();
        $this->assertSame('Poison', $sut->getType());
        $this->assertSame('POKEMON_TYPE_POISON', $sut->getGameMasterTypeName());
        $this->assertCount(2, $sut->getDoubleDamageFrom());
        $this->assertCount(5, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testGround(): void
    {
        $sut = PokemonType::Ground();
        $this->assertSame('Ground', $sut->getType());
        $this->assertSame('POKEMON_TYPE_GROUND', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(2, $sut->getHalfDamageFrom());
        $this->assertCount(1, $sut->getNoDamageFrom());
    }

    public function testIce(): void
    {
        $sut = PokemonType::ice();
        $this->assertSame('Ice', $sut->getType());
        $this->assertSame('POKEMON_TYPE_ICE', $sut->getGameMasterTypeName());
        $this->assertCount(4, $sut->getDoubleDamageFrom());
        $this->assertCount(1, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testPsychic(): void
    {
        $sut = PokemonType::Psychic();
        $this->assertSame('Psychic', $sut->getType());
        $this->assertSame('POKEMON_TYPE_PSYCHIC', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(2, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testDragon(): void
    {
        $sut = PokemonType::Dragon();
        $this->assertSame('Dragon', $sut->getType());
        $this->assertSame('POKEMON_TYPE_DRAGON', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(4, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testWater(): void
    {
        $sut = PokemonType::Water();
        $this->assertSame('Water', $sut->getType());
        $this->assertSame('POKEMON_TYPE_WATER', $sut->getGameMasterTypeName());
        $this->assertCount(2, $sut->getDoubleDamageFrom());
        $this->assertCount(4, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testRock(): void
    {
        $sut = PokemonType::rock();
        $this->assertSame('Rock', $sut->getType());
        $this->assertSame('POKEMON_TYPE_ROCK', $sut->getGameMasterTypeName());
        $this->assertCount(5, $sut->getDoubleDamageFrom());
        $this->assertCount(4, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testNone(): void
    {
        $sut = PokemonType::none();
        $this->assertSame('None', $sut->getType());
        $this->assertSame('POKEMON_TYPE_NONE', $sut->getGameMasterTypeName());
        $this->assertCount(0, $sut->getDoubleDamageFrom());
        $this->assertCount(0, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testFairy(): void
    {
        $sut = PokemonType::Fairy();
        $this->assertSame('Fairy', $sut->getType());
        $this->assertSame('POKEMON_TYPE_FAIRY', $sut->getGameMasterTypeName());
        $this->assertCount(2, $sut->getDoubleDamageFrom());
        $this->assertCount(3, $sut->getHalfDamageFrom());
        $this->assertCount(1, $sut->getNoDamageFrom());
    }

    public function testFlying(): void
    {
        $sut = PokemonType::Flying();
        $this->assertSame('Flying', $sut->getType());
        $this->assertSame('POKEMON_TYPE_FLYING', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(3, $sut->getHalfDamageFrom());
        $this->assertCount(1, $sut->getNoDamageFrom());
    }

    public function testGrass(): void
    {
        $sut = PokemonType::Grass();
        $this->assertSame('Grass', $sut->getType());
        $this->assertSame('POKEMON_TYPE_GRASS', $sut->getGameMasterTypeName());
        $this->assertCount(5, $sut->getDoubleDamageFrom());
        $this->assertCount(4, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }

    public function testNormal(): void
    {
        $sut = PokemonType::Normal();
        $this->assertSame('Normal', $sut->getType());
        $this->assertSame('POKEMON_TYPE_NORMAL', $sut->getGameMasterTypeName());
        $this->assertCount(1, $sut->getDoubleDamageFrom());
        $this->assertCount(0, $sut->getHalfDamageFrom());
        $this->assertCount(1, $sut->getNoDamageFrom());
    }

    public function testFighting(): void
    {
        $sut = PokemonType::Fighting();
        $this->assertSame('Fighting', $sut->getType());
        $this->assertSame('POKEMON_TYPE_FIGHTING', $sut->getGameMasterTypeName());
        $this->assertCount(3, $sut->getDoubleDamageFrom());
        $this->assertCount(3, $sut->getHalfDamageFrom());
        $this->assertCount(0, $sut->getNoDamageFrom());
    }
}
