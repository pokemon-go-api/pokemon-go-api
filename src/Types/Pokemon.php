<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\EvolutionBranch;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonStats;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolution;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolutionBranch;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolutionCamera;

use function preg_match;
use function str_contains;
use function str_replace;

final class Pokemon
{
    private int $dexNr;
    private string $id;
    private string $formId;
    private PokemonType $typePrimary;
    private PokemonType $typeSecondary;

    private PokemonStats $stats;

    private PokemonForm|null $pokemonForm = null;

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

    /** @var array<int, EvolutionBranch|TemporaryEvolutionBranch> */
    private array $evolutions = [];

    private string|null $pokemonClass = null;

    /**
     * @param array{
     *     pokemonId: string,
     *     type: string,
     *     type2?: string,
     *     pokemonClass?: string,
     *     stats: PokemonStats,
     *     quickMoves: list<string>,
     *     cinematicMoves: list<string>,
     *     eliteQuickMove?: list<string>,
     *     eliteCinematicMove?: list<string>,
     *     evolutionBranch: list<EvolutionBranch|TemporaryEvolutionBranch>,
     *     tempEvoOverrides: list<TemporaryEvolution|TemporaryEvolutionCamera>
     * } $pokemonSettings
     */
    public function __construct(
        string $templateId,
        array $pokemonSettings,
    ) {
        if (
            ! preg_match(
                '~^V(?<id>\d{4})_POKEMON_(?<name>.*)$~i',
                $templateId,
                $pokemonParts,
            )
        ) {
            throw new Exception('Invalid pokemon template ID', 1766499467296);
        }

        $this->dexNr       = (int) $pokemonParts['id'];
        $this->id          = $pokemonSettings['pokemonId'];
        $this->formId      = $pokemonParts['name'];
        $this->typePrimary = PokemonType::createFromPokemonType($pokemonSettings['type']);

        if (isset($pokemonSettings['type2'])) {
            $this->typeSecondary = PokemonType::createFromPokemonType($pokemonSettings['type2']);
        } else {
            $this->typeSecondary = PokemonType::none();
        }

        $this->pokemonClass = $pokemonSettings['pokemonClass'] ?? null;
        $this->stats        = $pokemonSettings['stats'];

        $this->quickMoveNames          = $pokemonSettings['quickMoves'] ?? [];
        $this->cinematicMoveNames      = $pokemonSettings['cinematicMoves'] ?? [];
        $this->eliteQuickMoveNames     = $pokemonSettings['eliteQuickMove'] ?? [];
        $this->eliteCinematicMoveNames = $pokemonSettings['eliteCinematicMove'] ?? [];

        $this->evolutions = $pokemonSettings['evolutionBranch'] ?? [];

        foreach ($pokemonSettings['tempEvoOverrides'] ?? [] as $temporaryEvolution) {
            if (! ($temporaryEvolution instanceof TemporaryEvolution)) {
                continue;
            }

            $temporaryEvolution->setPokemonId($this->id);
            $this->temporaryEvolutions[] = $temporaryEvolution;
        }
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

    public function getStats(): PokemonStats|null
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
            if ($pokemonImage->getAssetBundleSuffix() === 'GIGANTAMAX') {
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

        return $copy;
    }

    public function overwriteDefaultPokemonForm(PokemonForm $pokemonForm): void
    {
        $this->formId      = $pokemonForm->getId();
        $this->pokemonForm = $pokemonForm;
        unset($this->pokemonRegionForms[$pokemonForm->getId()]);
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

         return $this->pokemonForm->getAssetBundleSuffix();
    }

    public function isSameFormAsBasePokemon(Pokemon $pokemon): bool
    {
        $pokemonStats = $pokemon->getStats();
        $formStats    = $this->getStats();

        return $this->getTypePrimary()->getType() === $pokemon->getTypePrimary()->getType()
            && $this->getTypeSecondary()->getType() === $pokemon->getTypeSecondary()->getType()
            && (
                $pokemonStats instanceof PokemonStats && $formStats instanceof PokemonStats
                && $formStats->getAttack() === $pokemonStats->getAttack()
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
        $pokemonForm            = $this->getPokemonForm();
        $assetBundleId          = null;
        $assetBundleSuffix      = $this->getFormId();
        $assetBundleSuffixFixed = null;
        if ($pokemonForm instanceof PokemonForm) {
            $assetBundleId     = $pokemonForm->getAssetBundleValue();
            $assetBundleSuffix = $pokemonForm->getAssetBundleSuffix() ?? $pokemonForm->getFormOnlyId();
        }

        if ($temporaryEvolution instanceof TemporaryEvolution) {
            $assetBundleId     = $temporaryEvolution->getAssetsBundleId();
            $assetBundleSuffix = $temporaryEvolution->getAssetsAddressableSuffix();
        }

        $assetBundleSuffixFixed = str_replace($this->getId() . '_', '', $assetBundleSuffix);

        foreach ($this->pokemonImages as $pokemonImage) {
            if (
                ($pokemonImage->getAssetBundleSuffix() === $assetBundleSuffix
                || $pokemonImage->getAssetBundleSuffix() === $assetBundleSuffixFixed)
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

        foreach ($this->pokemonImages as $pokemonImage) {
            if ($pokemonImage->getAssetBundleSuffix() === null) {
                return $pokemonImage;
            }
        }

        return $this->pokemonImages[0] ?? null;
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
        $copy->temporaryEvolutions = $temporaryEvolutions;

        return $copy;
    }
}
