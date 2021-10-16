<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;
use stdClass;

use function assert;
use function count;
use function preg_match;
use function strpos;

final class Pokemon
{
    private int $dexNr;
    private string $id;
    private string $formId;
    private PokemonType $typePrimary;
    private PokemonType $typeSecondary;
    private ?PokemonStats $stats      = null;
    private ?PokemonForm $pokemonForm = null;
    /** @var TemporaryEvolution[] */
    private array $temporaryEvolutions = [];
    /** @var string[] */
    private array $quickMoveNames = [];
    /** @var string[] */
    private array $cinematicMoveNames = [];
    /** @var string[] */
    private array $eliteQuickMoveNames = [];
    /** @var string[] */
    private array $eliteCinematicMoveNames = [];
    /** @var array<string, Pokemon> */
    private array $pokemonRegionForms = [];
    /** @var array<int, PokemonImage> */
    private array $pokemonImages = [];
    /** @var array<int, EvolutionBranch> */
    private array $evolutions = [];

    public function __construct(
        int $dexNr,
        string $id,
        string $formId,
        PokemonType $typePrimary,
        ?PokemonType $typeSecondary
    ) {
        $this->dexNr         = $dexNr;
        $this->id            = $id;
        $this->formId        = $formId;
        $this->typePrimary   = $typePrimary;
        $this->typeSecondary = $typeSecondary ?? PokemonType::none();
    }

    public static function createFromGameMaster(stdClass $pokemonData): self
    {
        $pokemonParts    = [];
        $pregMatchResult = preg_match(
            '~^V(?<id>\d{4})_POKEMON_(?<name>.*)$~i',
            $pokemonData->templateId ?? '',
            $pokemonParts
        );
        if ($pregMatchResult < 1) {
            throw new Exception('Invalid input data provided', 1608127311711);
        }

        $pokemonSettings = $pokemonData->pokemonSettings;

        $secondaryType = null;
        if (isset($pokemonSettings->type2)) {
            $secondaryType = PokemonType::createFromPokemonType($pokemonSettings->type2);
        }

        $pokemon = new self(
            (int) $pokemonParts['id'],
            $pokemonSettings->pokemonId,
            $pokemonParts['name'],
            PokemonType::createFromPokemonType($pokemonSettings->type),
            $secondaryType
        );

        if (isset($pokemonSettings->stats->baseStamina)) {
            assert($pokemonSettings->stats instanceof stdClass);
            $pokemon->stats = new PokemonStats(
                $pokemonSettings->stats->baseStamina,
                $pokemonSettings->stats->baseAttack,
                $pokemonSettings->stats->baseDefense,
            );
        }

        $pokemon->quickMoveNames          = $pokemonSettings->quickMoves ?? [];
        $pokemon->cinematicMoveNames      = $pokemonSettings->cinematicMoves ?? [];
        $pokemon->eliteQuickMoveNames     = $pokemonSettings->eliteQuickMove ?? [];
        $pokemon->eliteCinematicMoveNames = $pokemonSettings->eliteCinematicMove ?? [];

        foreach ($pokemonSettings->evolutionBranch ?? [] as $evolutionBranch) {
            if (! isset($evolutionBranch->evolution)) {
                continue;
            }

            assert($evolutionBranch instanceof stdClass);
            $pokemon->evolutions[] = EvolutionBranch::createFromGameMaster($evolutionBranch);
        }

        $tempEvos = [];
        foreach ($pokemonSettings->tempEvoOverrides ?? [] as $evolutionBranch) {
            $tempEvos[] = TemporaryEvolution::createFromGameMaster(
                $evolutionBranch,
                $pokemonSettings->pokemonId
            );
        }

        return $pokemon->withAddedTemporaryEvolutions(...$tempEvos);
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

    public function getStats(): ?PokemonStats
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
        return count($this->temporaryEvolutions) > 0;
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

        return $copy;
    }

    public function overwriteDefaultPokemonForm(PokemonForm $pokemonForm): void
    {
        $this->formId      = $pokemonForm->getId();
        $this->pokemonForm = $pokemonForm;
        unset($this->pokemonRegionForms[$pokemonForm->getId()]);
    }

    public function getPokemonForm(): ?PokemonForm
    {
        return $this->pokemonForm;
    }

    /**
     * @return list<EvolutionBranch>
     */
    public function getEvolutions(): array
    {
        return $this->evolutions;
    }

    public function getAssetsBundleId(): int
    {
        if ($this->pokemonForm === null) {
            return 0;
        }

         return $this->pokemonForm->getAssetBundleValue();
    }

    public function getAssetBundleSuffix(): ?string
    {
        if ($this->pokemonForm === null) {
            return null;
        }

         return $this->pokemonForm->getAssetBundleSuffix();
    }

    public function isSameFormAsBasePokemon(Pokemon $pokemon): bool
    {
        $pokemonStats = $pokemon->getStats();
        $formStats    = $this->getStats();

        return $this->getTypePrimary()->getType() === $pokemon->getTypePrimary()->getType()
            && $this->getTypeSecondary()->getType() === $pokemon->getTypeSecondary()->getType()
            && (
                $pokemonStats !== null && $formStats !== null
                && $formStats->getAttack() === $pokemonStats->getAttack()
                && $formStats->getDefense() === $pokemonStats->getDefense()
                && $formStats->getStamina() === $pokemonStats->getStamina()
            ) && strpos($this->getFormId(), '_FEMALE') === false;
    }

    public function getPokemonImage(
        ?TemporaryEvolution $temporaryEvolution = null,
        ?string $costume = null
    ): ?PokemonImage {
        $pokemonForm       = $this->getPokemonForm();
        $assetBundleId     = null;
        $assetBundleSuffix = null;
        if ($pokemonForm !== null) {
            $assetBundleId     = $pokemonForm->getAssetBundleValue();
            $assetBundleSuffix = $pokemonForm->getAssetBundleSuffix() ?? $pokemonForm->getFormOnlyId();
        }

        if ($temporaryEvolution !== null) {
            $assetBundleId     = $temporaryEvolution->getAssetsBundleId();
            $assetBundleSuffix = $temporaryEvolution->getAssetsAddressableSuffix();
        }

        foreach ($this->pokemonImages as $pokemonImage) {
            if (
                $pokemonImage->getAssetBundleSuffix() === $assetBundleSuffix
                && $pokemonImage->getCostume() === $costume
            ) {
                return $pokemonImage;
            }
        }

        foreach ($this->pokemonImages as $pokemonImage) {
            if (
                $pokemonImage->getAssetBundleValue() === $assetBundleId
                && $pokemonImage->getAssetBundleSuffix() === null
                && $pokemonImage->getCostume() === $costume
            ) {
                return $pokemonImage;
            }

            if (
                $assetBundleId === 0
                && $pokemonImage->getAssetBundleValue() === null
                && $pokemonImage->getAssetBundleSuffix() === null
                && $pokemonImage->getCostume() === $costume
            ) {
                return $pokemonImage;
            }
        }

        return $this->pokemonImages[0] ?? null;
    }

    /**
     * @param array<int, PokemonImage> $images
     */
    public function withAddedImages(array $images): self
    {
        $copy                = clone $this;
        $copy->pokemonImages = $images;

        return $copy;
    }

    public function withAddedTemporaryEvolutions(TemporaryEvolution ...$temporaryEvolutions): self
    {
        $copy                      = clone $this;
        $copy->temporaryEvolutions = $temporaryEvolutions;

        return $copy;
    }
}
