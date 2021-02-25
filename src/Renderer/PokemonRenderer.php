<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Renderer;

use PokemonGoLingen\PogoAPI\Collections\AttacksCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Util\GenerationDeterminer;

use function array_map;
use function sprintf;

final class PokemonRenderer
{
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%s.png';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_SHINY_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%s_shiny.png';

    private TranslationCollectionCollection $translations;

    public function __construct(TranslationCollectionCollection $translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return mixed[]
     */
    public function render(
        Pokemon $pokemon,
        AttacksCollection $attacksCollection
    ): array {
        $names = [];
        foreach ($this->translations->getCollections() as $translationCollection) {
            $names[$translationCollection->getLanguageName()] = PokemonNameRenderer::renderPokemonName(
                $pokemon,
                $translationCollection
            );
        }

        $struct = [
            'id'                  => $pokemon->getId(),
            'formId'              => $pokemon->getFormId(),
            'dexNr'               => $pokemon->getDexNr(),
            'generation'          => GenerationDeterminer::fromDexNr($pokemon->getDexNr()),
            'names'               => $names,
            'stats'               => $pokemon->getStats(),
            'primaryType'         => $this->renderType($pokemon->getTypePrimary(), $this->translations),
            'secondaryType'       => $this->renderType($pokemon->getTypeSecondary(), $this->translations),
            'quickMoves'          => $this->renderAttacks(
                $pokemon->getQuickMoveNames(),
                $attacksCollection,
                $this->translations
            ),
            'cinematicMoves'      => $this->renderAttacks(
                $pokemon->getCinematicMoveNames(),
                $attacksCollection,
                $this->translations
            ),
            'eliteQuickMoves'     => $this->renderAttacks(
                $pokemon->getEliteQuickMoveNames(),
                $attacksCollection,
                $this->translations
            ),
            'eliteCinematicMoves' => $this->renderAttacks(
                $pokemon->getEliteCinematicMoveNames(),
                $attacksCollection,
                $this->translations
            ),
            'assets' => [
                'image'      => $this->buildPokemonImageUrl($pokemon, $pokemon->getAssetsBundleId(), false),
                'shinyImage' => $this->buildPokemonImageUrl($pokemon, $pokemon->getAssetsBundleId(), true),
            ],
            'regionForms'         => array_map(
                fn (Pokemon $pokemon): array => $this->render($pokemon, $attacksCollection),
                $pokemon->getPokemonRegionForms()
            ),
            'hasMegaEvolution'    => $pokemon->hasTemporaryEvolutions(),
            'megaEvolutions'      => $this->renderMegaEvolutions($pokemon, $this->translations),
        ];

        return $struct;
    }

    /**
     * @return array<string, mixed>
     */
    private function renderMegaEvolutions(Pokemon $pokemon, TranslationCollectionCollection $translations): array
    {
        $output = [];
        foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
            $tmpNames = [];
            foreach ($translations->getCollections() as $translationCollection) {
                $tmpNames[$translationCollection->getLanguageName()] = PokemonNameRenderer::renderPokemonMegaName(
                    $pokemon,
                    $temporaryEvolution->getId(),
                    $translationCollection
                );
            }

            $output[$temporaryEvolution->getId()] = [
                'id'            => $temporaryEvolution->getId(),
                'names'         => $tmpNames,
                'stats'         => $temporaryEvolution->getStats(),
                'primaryType'   => $this->renderType($temporaryEvolution->getTypePrimary(), $translations),
                'secondaryType' => $this->renderType($temporaryEvolution->getTypeSecondary(), $translations),
                'assets'        => [
                    'image' => $this->buildPokemonImageUrl(
                        $pokemon,
                        $temporaryEvolution->getAssetsBundleId(),
                        false
                    ),
                    'shinyImage' => $this->buildPokemonImageUrl(
                        $pokemon,
                        $temporaryEvolution->getAssetsBundleId(),
                        true
                    ),
                ],
            ];
        }

        return $output;
    }

    /**
     * @return array<string, string|array<string, string|null>>
     */
    private function renderType(PokemonType $type, TranslationCollectionCollection $translations): ?array
    {
        if ($type->getType() === PokemonType::NONE) {
            return null;
        }

        $names = [];
        foreach ($translations->getCollections() as $translationCollection) {
            $names[$translationCollection->getLanguageName()] = $translationCollection->getTypeName(
                $type->getGameMasterTypeName()
            );
        }

        return [
            'type'  => $type->getGameMasterTypeName(),
            'names' => $names,
        ];
    }

    /**
     * @param string[] $moves
     *
     * @return array<string, mixed>
     */
    private function renderAttacks(
        array $moves,
        AttacksCollection $attacksCollection,
        TranslationCollectionCollection $translations
    ): array {
        $out = [];
        foreach ($moves as $moveName) {
            $attack = $attacksCollection->getByName($moveName);
            if ($attack === null) {
                continue;
            }

            $names = [];
            foreach ($translations->getCollections() as $translationCollection) {
                $names[$translationCollection->getLanguageName()] = $translationCollection->getMoveName(
                    $attack->getId()
                );
            }

            $combatMove = $attack->getCombatMove();
            $combat     = null;
            if ($combatMove !== null) {
                $combat = [
                    'energy' => $combatMove->getEnergy(),
                    'power'  => $combatMove->getPower(),
                    'turns' => 1 + $combatMove->getDurationTurns(),
                    'buffs' => null,
                ];
                $buffs  = $combatMove->getBuffs();
                if ($buffs !== null) {
                    $combat['buffs'] = [
                        'activationChance' => $buffs->getActivationChance(),
                        'attackerAttackStatsChange' => $buffs->getAttackerAttackStatStageChange(),
                        'attackerDefenseStatsChange' => $buffs->getAttackerDefenseStatStageChange(),
                        'targetAttackStatsChange' => $buffs->getTargetAttackStatStageChange(),
                        'targetDefenseStatsChange' => $buffs->getTargetDefenseStatStageChange(),
                    ];
                }
            }

            $out[$moveName] = [
                'id'         => $moveName,
                'power'      => $attack->getPower(),
                'energy'     => $attack->getEnergy(),
                'durationMs' => $attack->getDurationMs(),
                'type'       => $this->renderType($attack->getPokemonType(), $translations),
                'names'      => $names,
                'combat'     => $combat,
            ];
        }

        return $out;
    }

    private function buildPokemonImageUrl(Pokemon $pokemon, ?int $assetBundleId, bool $shiny): string
    {
        $bundleSuffix                 = sprintf('%03d_%02d', $pokemon->getDexNr(), $assetBundleId ?? 0);
        $pokemonForm                  = $pokemon->getPokemonForm();
        $pokemonFormAssetBundleSuffix = $pokemonForm !== null ? $pokemonForm->getAssetBundleSuffix() : null;
        if ($pokemonFormAssetBundleSuffix !== null) {
            $bundleSuffix = $pokemonFormAssetBundleSuffix;
        }

        if ($shiny) {
            return sprintf(self::ASSETS_BASE_SHINY_URL, $bundleSuffix);
        }

        return sprintf(self::ASSETS_BASE_URL, $bundleSuffix);
    }
}
