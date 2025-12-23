<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

final class TemporaryEvolutionSettings
{
    public readonly string $pokemonId;
    /**
     * @var list<array{evoId: string, bundleId: int}>
     */
    public readonly array $evolutions;
    
    /** 
     * @param array{
     *   pokemonId: string,
     *   temporaryEvolutions: list<array{
     *       temporaryEvolutionId: string,
     *       assetBundleValue: int,
     *   }>
     * } $temporaryEvolutionSettings */
    public function __construct(
        array $temporaryEvolutionSettings,
    ) {
        $this->pokemonId = $temporaryEvolutionSettings['pokemonId'];
        $evolutions = [];
        foreach ($temporaryEvolutionSettings['temporaryEvolutions'] as $temporaryEvolution) {
            $evolutions[] = ['evoId' => $temporaryEvolution['temporaryEvolutionId'], 'bundleId' => $temporaryEvolution['assetBundleValue']];
        }
        $this->evolutions = $evolutions;
    }
}
