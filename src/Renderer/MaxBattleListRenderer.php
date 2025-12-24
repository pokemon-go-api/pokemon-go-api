<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer;

use PokemonGoApi\PogoAPI\Collections\MaxBattleCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoApi\PogoAPI\Types\MaxBattle;
use PokemonGoApi\PogoAPI\Types\MaxBattleLevel;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Util\CpCalculator;

use function array_filter;
use function array_map;

final class MaxBattleListRenderer
{
    /** @return array<string, array<int, mixed[]>> */
    public function buildList(
        MaxBattleCollection $maxBattles,
        TranslationCollectionCollection $translationCollection,
    ): array {
        $bosses = [];
        foreach ($maxBattles->toArray() as $maxBattle) {
            $maxBattlePokemon = $maxBattle->pokemon;

            if ($maxBattle->maxBattleLevel === MaxBattleLevel::LEVEL_6) {
                $maxBattlePokemon->setForm(
                    new PokemonForm(
                        $maxBattlePokemon->getId(),
                        'GIGANTAMAX',
                        false,
                        null,
                        'GIGANTAMAX',
                    ),
                );
            }

            $bossTypes = [
                $maxBattle->pokemon->getTypePrimary(),
                $maxBattle->pokemon->getTypeSecondary(),
            ];

            $bossTypeNames = array_map(
                static fn (PokemonType $pokemonType): string => $pokemonType->getType(),
                array_filter(
                    $bossTypes,
                    static fn (PokemonType $pokemonType): bool => $pokemonType->getType() !== PokemonType::NONE,
                ),
            );

            $bossStats    = $maxBattle->pokemon->getStats();
            $pokemonImage = $maxBattle->pokemon->getPokemonImage();
            $raidData     = [
                'id'           => $maxBattle->pokemon->getId(),
                'assets'        => $pokemonImage ? [
                    'image' => $pokemonImage->buildUrl(false),
                    'shinyImage' => $pokemonImage->buildUrl(true),
                ] : null,
                'level'        => $maxBattle->maxBattleLevel->value,
                'names'        => $this->getNames($maxBattle, $translationCollection),
                'shiny'        => $maxBattle->shinyAvailable,
                'types'        => $bossTypeNames,
                'cpRange'      => [
                    CpCalculator::calculateRaidMinCp($bossStats),
                    CpCalculator::calculateRaidMaxCp($bossStats),
                ],
            ];

            $bosses['tier_' . $maxBattle->maxBattleLevel->value][] = $raidData;
        }

        return $bosses;
    }

    /** @return array<string, string> */
    private function getNames(
        MaxBattle $maxBattle,
        TranslationCollectionCollection $translationCollectionCollection,
    ): array {
        $out = [];
        foreach ($translationCollectionCollection->getCollections() as $translationName => $translationCollection) {
            $pokemonName           = PokemonNameRenderer::renderPokemonName($maxBattle->pokemon, $translationCollection);
            $out[$translationName] = $pokemonName;
        }

        return $out;
    }
}
