<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Renderer;

use PokemonGoLingen\PogoAPI\Collections\AttacksCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Util\GenerationDeterminer;

use function array_map;
use function array_shift;
use function sprintf;

final class PokemonRenderer
{
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%03d_%02d.png';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_SHINY_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%03d_%02d_shiny.png';

    /** @var TranslationCollection[] */
    private array $translations;

    /** @param TranslationCollection[] $translations */
    public function __construct(array $translations)
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
        foreach ($this->translations as $translationCollection) {
            $names[$translationCollection->getLanguageName()] = $translationCollection->getPokemonName(
                $pokemon->getDexNr()
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
                'image'      => sprintf(self::ASSETS_BASE_URL, $pokemon->getDexNr(), $pokemon->getAssetsBundleId()),
                'shinyImage' => sprintf(
                    self::ASSETS_BASE_SHINY_URL,
                    $pokemon->getDexNr(),
                    $pokemon->getAssetsBundleId()
                ),
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
     * @param TranslationCollection[] $translations
     *
     * @return array<string, mixed>
     */
    private function renderMegaEvolutions(Pokemon $pokemon, array $translations): array
    {
        $extraNames = [];
        foreach ($translations as $translationCollection) {
            $extraNames[$translationCollection->getLanguageName()] = $translationCollection->getPokemonMegaNames(
                $pokemon->getDexNr()
            );
        }

        $output = [];
        foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
            $tmpNames = [];
            foreach ($extraNames as $language => $names) {
                $tmpNames[$language] = array_shift($extraNames[$language]);
            }

            $output[$temporaryEvolution->getId()] = [
                'id'            => $temporaryEvolution->getId(),
                'names'         => $tmpNames,
                'stats'         => $temporaryEvolution->getStats(),
                'primaryType'   => $this->renderType($temporaryEvolution->getTypePrimary(), $translations),
                'secondaryType' => $this->renderType($temporaryEvolution->getTypeSecondary(), $translations),
                'assets'        => [
                    'image' => sprintf(
                        self::ASSETS_BASE_URL,
                        $pokemon->getDexNr(),
                        $temporaryEvolution->getAssetsBundleId()
                    ),
                    'shinyImage' => sprintf(
                        self::ASSETS_BASE_SHINY_URL,
                        $pokemon->getDexNr(),
                        $temporaryEvolution->getAssetsBundleId()
                    ),
                ],
            ];
        }

        return $output;
    }

    /**
     * @param TranslationCollection[] $translations
     *
     * @return array<string, string|array<string, string|null>>
     */
    private function renderType(?PokemonType $type, array $translations): ?array
    {
        if ($type === null) {
            return null;
        }

        $names = [];
        foreach ($translations as $translationCollection) {
            $names[$translationCollection->getLanguageName()] = $translationCollection->getTypeName(
                $type->getTypeName()
            );
        }

        return [
            'type'  => $type->getTypeName(),
            'names' => $names,
        ];
    }

    /**
     * @param string[]                $moves
     * @param TranslationCollection[] $translations
     *
     * @return array<string, mixed>
     */
    private function renderAttacks(array $moves, AttacksCollection $attacksCollection, array $translations): array
    {
        $out = [];
        foreach ($moves as $moveName) {
            $attack = $attacksCollection->getByName($moveName);
            if ($attack === null) {
                continue;
            }

            $names = [];
            foreach ($translations as $translationCollection) {
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
                ];
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
}
