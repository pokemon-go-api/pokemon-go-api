<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;

use function method_exists;
use function str_replace;
use function strtolower;
use function strtoupper;

final class PokemonType
{
    public const NONE      = 'None';
    private const NORMAL   = 'Normal';
    private const FIGHTING = 'Fighting';
    private const FLYING   = 'Flying';
    private const POISON   = 'Poison';
    private const GROUND   = 'Ground';
    private const ROCK     = 'Rock';
    private const BUG      = 'Bug';
    private const GHOST    = 'Ghost';
    private const STEEL    = 'Steel';
    private const FIRE     = 'Fire';
    private const WATER    = 'Water';
    private const GRASS    = 'Grass';
    private const ELECTRIC = 'Electric';
    private const PSYCHIC  = 'Psychic';
    private const ICE      = 'Ice';
    private const DRAGON   = 'Dragon';
    private const DARK     = 'Dark';
    private const FAIRY    = 'Fairy';

    public const ALL_TYPES = [
        self::NORMAL,
        self::FIGHTING,
        self::FLYING,
        self::POISON,
        self::GROUND,
        self::ROCK,
        self::BUG,
        self::GHOST,
        self::STEEL,
        self::FIRE,
        self::WATER,
        self::GRASS,
        self::ELECTRIC,
        self::PSYCHIC,
        self::ICE,
        self::DRAGON,
        self::DARK,
        self::FAIRY,
    ];

    /**
     * @param array<int, string> $doubleDamageFrom
     * @param array<int, string> $halfDamageFrom
     * @param array<int, string> $noDamageFrom
     */
    public function __construct(
        private string $type,
        private array $doubleDamageFrom,
        private array $halfDamageFrom,
        private array $noDamageFrom,
    ) {
    }

    /** @return array<int, string> */
    public function getDoubleDamageFrom(): array
    {
        return $this->doubleDamageFrom;
    }

    /** @return array<int, string> */
    public function getHalfDamageFrom(): array
    {
        return $this->halfDamageFrom;
    }

    /** @return array<int, string> */
    public function getNoDamageFrom(): array
    {
        return $this->noDamageFrom;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getGameMasterTypeName(): string
    {
        return strtoupper('POKEMON_TYPE_' . $this->type);
    }

    public function __toString(): string
    {
        return $this->type;
    }

    public static function createFromPokemonType(string $pokemonTypeId): self
    {
        $typeNormalized = strtolower(str_replace('POKEMON_TYPE_', '', $pokemonTypeId));
        if (method_exists(self::class, $typeNormalized)) {
            return self::{$typeNormalized}();
        }

        throw new Exception('Type does not exists');
    }

    public static function none(): self
    {
        return new self(
            self::NONE,
            [],
            [],
            [],
        );
    }

    public static function normal(): self
    {
        return new self(
            self::NORMAL,
            [self::FIGHTING],
            [],
            [self::GHOST],
        );
    }

    public static function fighting(): self
    {
        return new self(
            self::FIGHTING,
            [self::FLYING, self::PSYCHIC, self::FAIRY],
            [self::ROCK, self::BUG, self::DARK],
            [],
        );
    }

    public static function flying(): self
    {
        return new self(
            self::FLYING,
            [self::ROCK, self::ELECTRIC, self::ICE],
            [self::FIGHTING, self::BUG, self::GRASS],
            [self::GROUND],
        );
    }

    public static function poison(): self
    {
        return new self(
            self::POISON,
            [self::GROUND, self::PSYCHIC],
            [self::FIGHTING, self::POISON, self::BUG, self::GRASS, self::FAIRY],
            [],
        );
    }

    public static function ground(): self
    {
        return new self(
            self::GROUND,
            [self::WATER, self::GRASS, self::ICE],
            [self::POISON, self::ROCK],
            [self::ELECTRIC],
        );
    }

    public static function rock(): self
    {
        return new self(
            self::ROCK,
            [self::FIGHTING, self::GROUND, self::STEEL, self::WATER, self::GRASS],
            [self::NORMAL, self::FLYING, self::POISON, self::FIRE],
            [],
        );
    }

    public static function bug(): self
    {
        return new self(
            self::BUG,
            [self::FLYING, self::ROCK, self::FIRE],
            [self::FIGHTING, self::GROUND, self::GRASS],
            [],
        );
    }

    public static function ghost(): self
    {
        return new self(
            self::GHOST,
            [self::GHOST, self::DARK],
            [self::POISON, self::BUG],
            [self::NORMAL, self::FIGHTING],
        );
    }

    public static function steel(): self
    {
        return new self(
            self::STEEL,
            [self::FIGHTING, self::GROUND, self::FIRE],
            [
                self::NORMAL,
                self::FLYING,
                self::ROCK,
                self::BUG,
                self::STEEL,
                self::GRASS,
                self::PSYCHIC,
                self::ICE,
                self::DRAGON,
                self::FAIRY,
            ],
            [self::POISON],
        );
    }

    public static function fire(): self
    {
        return new self(
            self::FIRE,
            [self::GROUND, self::ROCK, self::WATER],
            [self::BUG, self::STEEL, self::FIRE, self::GRASS, self::ICE, self::FAIRY],
            [],
        );
    }

    public static function water(): self
    {
        return new self(
            self::WATER,
            [self::GRASS, self::ELECTRIC],
            [self::STEEL, self::FIRE, self::WATER, self::ICE],
            [],
        );
    }

    public static function grass(): self
    {
        return new self(
            self::GRASS,
            [self::FLYING, self::POISON, self::BUG, self::FIRE, self::ICE],
            [self::GROUND, self::WATER, self::GRASS, self::ELECTRIC],
            [],
        );
    }

    public static function electric(): self
    {
        return new self(
            self::ELECTRIC,
            [self::GROUND],
            [self::FLYING, self::STEEL, self::ELECTRIC],
            [],
        );
    }

    public static function psychic(): self
    {
        return new self(
            self::PSYCHIC,
            [self::BUG, self::GHOST, self::DARK],
            [self::FIGHTING, self::PSYCHIC],
            [],
        );
    }

    public static function ice(): self
    {
        return new self(
            self::ICE,
            [self::FIGHTING, self::ROCK, self::STEEL, self::FIRE],
            [self::ICE],
            [],
        );
    }

    public static function dragon(): self
    {
        return new self(
            self::DRAGON,
            [self::ICE, self::DRAGON, self::FAIRY],
            [self::FIRE, self::WATER, self::GRASS, self::ELECTRIC],
            [],
        );
    }

    public static function dark(): self
    {
        return new self(
            self::DARK,
            [self::FIGHTING, self::BUG, self::FAIRY],
            [self::GHOST, self::DARK],
            [self::PSYCHIC],
        );
    }

    public static function fairy(): self
    {
        return new self(
            self::FAIRY,
            [self::POISON, self::STEEL],
            [self::FIGHTING, self::BUG, self::DARK],
            [self::DRAGON],
        );
    }
}
