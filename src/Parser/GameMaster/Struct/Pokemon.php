<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;
use Exception;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\PokemonType;

use function array_values;
use function preg_match;
use function str_contains;
use function str_replace;
use function str_starts_with;

final class Pokemon
{
    public function __construct(
        private int $dexNr,
        private string $id,
        private string $formId,
        private PokemonType $typePrimary,
        private PokemonType $typeSecondary,
        private PokemonStats $stats = new PokemonStats(0, 0, 0),
        private PokemonForm|null $pokemonForm = null,
        /** @var list<TemporaryEvolution> */
        private array $temporaryEvolutions = [],
        /** @var string[] */
        private array $quickMoveNames = [],
        /** @var string[] */
        private array $cinematicMoveNames = [],
        /** @var string[] */
        private array $eliteQuickMoveNames = [],
        /** @var string[] */
        private array $eliteCinematicMoveNames = [],
        /** @var array<string, Pokemon> */
        private array $pokemonRegionForms = [],
        /** @var array<int, PokemonImage> */
        private array $pokemonImages = [],
        /** @var list<EvolutionBranch|TemporaryEvolutionBranch> */
        private array $evolutions = [],
        private string|null $pokemonClass = null,
    ) {
    }

    /** @param array{ pokemonId: string, type: string, type2?: string, pokemonClass?: string, stats: PokemonStats, quickMoves: list<string>, cinematicMoves: list<string>, eliteQuickMove?: list<string>, eliteCinematicMove?: list<string>, evolutionBranch: list<EvolutionBranch|TemporaryEvolutionBranch>, tempEvoOverrides: list<TemporaryEvolution|TemporaryEvolutionCamera> } $pokemonSettings */
    #[Constructor]
    public static function fromArray(
        string $templateId,
        array $pokemonSettings,
    ): self {
        if (
            ! preg_match(
                '~^V(?<id>\d{4})_POKEMON_(?<name>.*)$~i',
                $templateId,
                $pokemonParts,
            )
        ) {
            throw new Exception('Invalid pokemon template ID', 1766499467296);
        }

        $typeSecondary = PokemonType::none();
        if (isset($pokemonSettings['type2'])) {
            $typeSecondary = PokemonType::createFromPokemonType($pokemonSettings['type2']);
        }

        $temporaryEvolutions = [];
        foreach ($pokemonSettings['tempEvoOverrides'] as $temporaryEvolution) {
            if (! ($temporaryEvolution instanceof TemporaryEvolution)) {
                continue;
            }

            $temporaryEvolution->setPokemonId($pokemonSettings['pokemonId']);
            $temporaryEvolutions[] = $temporaryEvolution;
        }

        return new self(
            (int) $pokemonParts['id'],
            $pokemonSettings['pokemonId'],
            $pokemonParts['name'],
            PokemonType::createFromPokemonType($pokemonSettings['type']),
            $typeSecondary,
            $pokemonSettings['stats'],
            null,
            $temporaryEvolutions,
            $pokemonSettings['quickMoves'],
            $pokemonSettings['cinematicMoves'],
            $pokemonSettings['eliteQuickMove'] ?? [],
            $pokemonSettings['eliteCinematicMove'] ?? [],
            [],
            [],
            $pokemonSettings['evolutionBranch'],
            $pokemonSettings['pokemonClass'] ?? null,
        );
    }

    public function getDexNr(): int
    {
        return $this->dexNr;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFormId(): string
    {
        return $this->formId;
    }

    public function getTypePrimary(): PokemonType
    {
        return $this->typePrimary;
    }

    public function getTypeSecondary(): PokemonType
    {
        return $this->typeSecondary;
    }

    public function getStats(): PokemonStats
    {
        return $this->stats;
    }

    /** @return string[] */
    public function getCinematicMoveNames(): array
    {
        return $this->cinematicMoveNames;
    }

    /** @return string[] */
    public function getEliteCinematicMoveNames(): array
    {
        return $this->eliteCinematicMoveNames;
    }

    /** @return string[] */
    public function getEliteQuickMoveNames(): array
    {
        return $this->eliteQuickMoveNames;
    }

    /** @return string[] */
    public function getQuickMoveNames(): array
    {
        return $this->quickMoveNames;
    }

    /** @return TemporaryEvolution[] */
    public function getTemporaryEvolutions(): array
    {
        return $this->temporaryEvolutions;
    }

    public function hasTemporaryEvolutions(): bool
    {
        return $this->temporaryEvolutions !== [];
    }

    public function hasGigantamax(): bool
    {
        foreach ($this->pokemonImages as $pokemonImage) {
            if ($pokemonImage->getForm() === 'GIGANTAMAX') {
                return true;
            }
        }

        return false;
    }

    public function withAddedPokemonRegionForm(Pokemon $pokemonRegionForm): self
    {
        $copy                                                      = clone $this;
        $copy->pokemonRegionForms[$pokemonRegionForm->getFormId()] = $pokemonRegionForm;

        return $copy;
    }

    /** @return array<string, Pokemon> */
    public function getPokemonRegionForms(): array
    {
        return $this->pokemonRegionForms;
    }

    public function withPokemonForm(PokemonForm $pokemonForm): self
    {
        $copy              = clone $this;
        $copy->pokemonForm = $pokemonForm;
        if (! str_starts_with($pokemonForm->getFormOnlyId(), $this->id)) {
            $copy->formId = $this->id . '_' . $pokemonForm->getFormOnlyId();
        } else {
            $copy->formId = $pokemonForm->getFormOnlyId();
        }

        return $copy;
    }

    public function getPokemonForm(): PokemonForm|null
    {
        return $this->pokemonForm;
    }

    /** @return list<EvolutionBranch|TemporaryEvolutionBranch> */
    public function getEvolutionsBranches(): array
    {
        return $this->evolutions;
    }

    public function getAssetsBundleId(): int
    {
        if (! $this->pokemonForm instanceof PokemonForm) {
            return 0;
        }

         return $this->pokemonForm->getAssetBundleValue();
    }

    public function getAssetBundleSuffix(): string|null
    {
        if (! $this->pokemonForm instanceof PokemonForm) {
            return null;
        }

         return $this->pokemonForm->getAssetForm();
    }

    public function isSameFormAsBasePokemon(Pokemon $pokemon): bool
    {
        $pokemonStats = $pokemon->getStats();
        $formStats    = $this->getStats();

        return $this->getTypePrimary()->getType() === $pokemon->getTypePrimary()->getType()
            && $this->getTypeSecondary()->getType() === $pokemon->getTypeSecondary()->getType()
            && (
                $formStats->getAttack() === $pokemonStats->getAttack()
                && $formStats->getDefense() === $pokemonStats->getDefense()
                && $formStats->getStamina() === $pokemonStats->getStamina()
            ) && ! str_contains($this->getFormId(), '_FEMALE');
    }

    public function getPokemonClass(): string|null
    {
        return $this->pokemonClass;
    }

    public function isUltraBeast(): bool
    {
        return $this->pokemonClass === 'POKEMON_CLASS_ULTRA_BEAST';
    }

    public function getPokemonImage(
        TemporaryEvolution|null $temporaryEvolution = null,
        string|null $costume = null,
    ): PokemonImage|null {
        $pokemonForm       = $this->getPokemonForm();
        $assetBundleSuffix = $this->getFormId();
        if ($pokemonForm instanceof PokemonForm) {
            $assetBundleSuffix = $pokemonForm->getAssetForm() ?? $pokemonForm->getFormOnlyId();
        }

        if ($temporaryEvolution instanceof TemporaryEvolution) {
            $assetBundleSuffix = $temporaryEvolution->getAssetsAddressableSuffix();
        }

        $assetBundleSuffixFixed = str_replace($this->getId() . '_', '', $assetBundleSuffix);

        $fallbackImage = null;
        foreach ($this->pokemonImages as $pokemonImage) {
            if (
                ($pokemonImage->getForm() === $assetBundleSuffix
                || $pokemonImage->getForm() === $assetBundleSuffixFixed)
                && $pokemonImage->getCostume() === $costume
            ) {
                return $pokemonImage;
            }

            if (
                $pokemonImage->getForm() !== null
                || $pokemonImage->getCostume() !== null
                || $pokemonImage->isShiny() !== false
                || $fallbackImage !== null
            ) {
                continue;
            }

            $fallbackImage = $pokemonImage;
        }

        return $fallbackImage;
    }

    /** @param array<int, PokemonImage> $images */
    public function withAddedImages(array $images): self
    {
        $copy                = clone $this;
        $copy->pokemonImages = $images;

        return $copy;
    }

    public function withAddedTemporaryEvolutions(TemporaryEvolution ...$temporaryEvolutions): self
    {
        $copy                      = clone $this;
        $copy->temporaryEvolutions = array_values($temporaryEvolutions);

        return $copy;
    }

    public function removeRegionForm(Pokemon $pokemonRegionForm): void
    {
        unset($this->pokemonRegionForms[$pokemonRegionForm->getFormId()]);
    }

    public function setForm(PokemonForm $form): void
    {
        $this->pokemonForm = $form;
    }
}
