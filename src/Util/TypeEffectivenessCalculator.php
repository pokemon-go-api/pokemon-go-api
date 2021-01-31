<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Util;

use PokemonGoLingen\PogoAPI\Types\PokemonType;

use function array_combine;
use function array_fill;
use function array_filter;
use function array_keys;
use function array_map;
use function array_multisort;
use function count;
use function round;
use function sprintf;

final class TypeEffectivenessCalculator
{
    /**
     * @return array<string, float>
     */
    public function getAllTypes(PokemonType $primaryType, PokemonType $secondaryType): array
    {
        $doubleEffective = [...$primaryType->getDoubleDamageFrom(), ...$secondaryType->getDoubleDamageFrom()];
        $halfEffective   = [...$primaryType->getHalfDamageFrom(), ...$secondaryType->getHalfDamageFrom()];
        $noEffective     = [...$primaryType->getNoDamageFrom(), ...$secondaryType->getNoDamageFrom()];

        $allTypes = array_combine(PokemonType::ALL_TYPES, array_fill(0, count(PokemonType::ALL_TYPES), 1));
        foreach ($doubleEffective as $type) {
            $allTypes[$type] *= 1.6;
        }

        foreach ($halfEffective as $type) {
            $allTypes[$type] *= .625;
        }

        foreach ($noEffective as $type) {
            $allTypes[$type] *= .390625;
        }

        $allTypeSortKeys = [];
        foreach ($allTypes as $key => $value) {
            $allTypeSortKeys[] = sprintf('%04f-%s', 100 - $value, $key);
        }

        array_multisort(
            $allTypeSortKeys,
            $allTypes
        );

        return array_map(
            static fn (float $input): float => round($input, 3),
            $allTypes
        );
    }

    /**
     * @return array<string, float>
     */
    public function getAllEffectiveTypes(PokemonType $primaryType, PokemonType $secondaryType): array
    {
        return array_filter(
            $this->getAllTypes($primaryType, $secondaryType),
            static fn (float $multiplicator): bool => $multiplicator > 1.0
        );
    }

    /**
     * @return array<string>
     */
    public function getOneAHalfEffectiveTypes(PokemonType $primaryType, PokemonType $secondaryType): array
    {
        return array_keys(array_filter(
            $this->getAllEffectiveTypes($primaryType, $secondaryType),
            static fn (float $multiplicator): bool => $multiplicator < 2.0
        ));
    }

    /**
     * @return array<string>
     */
    public function getDoubleEffectiveTypes(PokemonType $primaryType, PokemonType $secondaryType): array
    {
        return array_keys(array_filter(
            $this->getAllEffectiveTypes($primaryType, $secondaryType),
            static fn (float $multiplicator): bool => $multiplicator >= 2.0
        ));
    }
}
