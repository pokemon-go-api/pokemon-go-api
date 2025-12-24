<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;

final class TemporaryEvolutionSettings
{
    public function __construct(
        public readonly string $pokemonId,
        /** @var list<array{evoId: string, bundleId: int}> */
        public readonly array $evolutions,
    ) {
    }

    /** @param array{ pokemonId: string, temporaryEvolutions: list<array{ temporaryEvolutionId: string, assetBundleValue: int }> } $temporaryEvolutionSettings */
    #[Constructor]
    public static function fromArray(
        array $temporaryEvolutionSettings,
    ): self {
        $evolutions = [];
        foreach ($temporaryEvolutionSettings['temporaryEvolutions'] as $temporaryEvolution) {
            $evolutions[] = ['evoId' => $temporaryEvolution['temporaryEvolutionId'], 'bundleId' => $temporaryEvolution['assetBundleValue']];
        }

        return new self(
            $temporaryEvolutionSettings['pokemonId'],
            $evolutions,
        );
    }
}
