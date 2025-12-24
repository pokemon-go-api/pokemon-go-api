<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Cache\RuntimeCache;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;

class JsonMapper
{
    /**
     * @param class-string<T> $class
     * @param array<mixed>    $data
     *
     * @return T
     *
     * @template T of object
     */
    public static function map(string $class, array $data): object
    {
        return (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->allowUndefinedValues()
            ->withCache(
                new RuntimeCache(new FileSystemCache(
                    __DIR__ . '/../../data/cache/',
                )),
            )
            ->mapper()
            ->map(
                $class,
                Source::array($data)->camelCaseKeys(),
            );
    }
}
