<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Util\TypeEffectivenessCalculator;

use function array_filter;
use function array_keys;

#[CoversClass(TypeEffectivenessCalculator::class)]
#[UsesClass(PokemonType::class)]
class TypeEffectivenessCalculatorTest extends TestCase
{
    /** @param array<string, float> $expected */
    #[DataProvider('getEffectiveTypesDataProvider')]
    public function testGetEffectiveTypes(PokemonType $primaryType, PokemonType $secondaryType, array $expected): void
    {
        $calculator = new TypeEffectivenessCalculator();

        $effectiveTypes = $calculator->getAllTypes($primaryType, $secondaryType);
        $this->assertSame($expected, $effectiveTypes);

        $effectiveTypes       = $calculator->getAllEffectiveTypes($primaryType, $secondaryType);
        $expectedAllEffective = array_filter($expected, static fn (float $multiplier): bool => $multiplier > 1.0);
        $this->assertSame($expectedAllEffective, $effectiveTypes);

        $effectiveTypes          = $calculator->getDoubleEffectiveTypes($primaryType, $secondaryType);
        $expectedDoubleEffective = array_filter($expected, static fn (float $multiplier): bool => $multiplier > 2.0);
        $this->assertSame(array_keys($expectedDoubleEffective), $effectiveTypes);

        $effectiveTypes            = $calculator->getOneAHalfEffectiveTypes($primaryType, $secondaryType);
        $expectedOneAHalfEffective = array_filter(
            $expected,
            static fn (float $multiplier): bool => $multiplier > 1.0 && $multiplier < 2.0,
        );
        $this->assertSame(array_keys($expectedOneAHalfEffective), $effectiveTypes);
    }

    public function testMaxEffectiveTypeCombo(): void
    {
        $calculator        = new TypeEffectivenessCalculator();
        $allEffectiveTypes = $calculator->getAllEffectiveTypes(PokemonType::grass(), PokemonType::ice());
        $this->assertCount(7, $allEffectiveTypes);
    }

    /** @return iterable<string, array<int, PokemonType|array<string, float>>> */
    public static function getEffectiveTypesDataProvider(): iterable
    {
        yield 'normal-none' => [
            PokemonType::normal(),
            PokemonType::none(),
            [
                'Fighting' => 1.6,
                'Bug'      => 1.0,
                'Dark'     => 1.0,
                'Dragon'   => 1.0,
                'Electric' => 1.0,
                'Fairy'    => 1.0,
                'Fire'     => 1.0,
                'Flying'   => 1.0,
                'Grass'    => 1.0,
                'Ground'   => 1.0,
                'Ice'      => 1.0,
                'Normal'   => 1.0,
                'Poison'   => 1.0,
                'Psychic'  => 1.0,
                'Rock'     => 1.0,
                'Steel'    => 1.0,
                'Water'    => 1.0,
                'Ghost'    => 0.391,
            ],
        ];

        yield 'normal-rock' => [
            PokemonType::normal(),
            PokemonType::rock(),
            [
                'Fighting' => 2.56,
                'Grass'    => 1.6,
                'Ground'   => 1.6,
                'Steel'    => 1.6,
                'Water'    => 1.6,
                'Bug'      => 1.0,
                'Dark'     => 1.0,
                'Dragon'   => 1.0,
                'Electric' => 1.0,
                'Fairy'    => 1.0,
                'Ice'      => 1.0,
                'Psychic'  => 1.0,
                'Rock'     => 1.0,
                'Fire'     => .625,
                'Flying'   => .625,
                'Normal'   => .625,
                'Poison'   => .625,
                'Ghost'    => .391,
            ],
        ];

        yield 'dragon-fairy' => [
            PokemonType::dragon(),
            PokemonType::fairy(),
            [
                'Fairy'    => 1.6,
                'Ice'      => 1.6,
                'Poison'   => 1.6,
                'Steel'    => 1.6,
                'Flying'   => 1.0,
                'Ghost'    => 1.0,
                'Ground'   => 1.0,
                'Normal'   => 1.0,
                'Psychic'  => 1.0,
                'Rock'     => 1.0,
                'Bug'      => .625,
                'Dark'     => .625,
                'Dragon'   => .625,
                'Electric' => .625,
                'Fighting' => .625,
                'Fire'     => .625,
                'Grass'    => .625,
                'Water'    => .625,
            ],
        ];
    }
}
