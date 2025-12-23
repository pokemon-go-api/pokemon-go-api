<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use CuyZ\Valinor\MapperBuilder;

class GameMasterMapper
{
    /**
     * @param class-string<T>
     * @param array<mixed>    $data
     *
     * @return T
     *
     * @template T
     */
    public static function map(string $class, array $data): object
    {
        return (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->allowUndefinedValues()
            ->mapper()
            ->map(
                $class,
                $data,
            );
    }
}
