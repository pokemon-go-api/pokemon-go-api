<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use stdClass;

use function assert;
use function strpos;
use function substr;

final class TemporaryEvolution
{
    private string $id;
    private ?int $assetsBundleId = null;
    private PokemonType $typePrimary;
    private PokemonType $typeSecondary;
    private ?PokemonStats $stats = null;

    public function __construct(string $id, PokemonType $typePrimary, ?PokemonType $typeSecondary)
    {
        $this->id            = $id;
        $this->typePrimary   = $typePrimary;
        $this->typeSecondary = $typeSecondary ?? PokemonType::none();
    }

    public static function createFromGameMaster(stdClass $tempEvoOverride, string $basePokemonId): self
    {
        $secondaryType = null;
        if (isset($tempEvoOverride->typeOverride2)) {
            $secondaryType = PokemonType::createFromPokemonType($tempEvoOverride->typeOverride2);
        }

        $temporaryEvolution = new self(
            $basePokemonId . substr($tempEvoOverride->tempEvoId, 14), // trim TEMP_EVOLUTION
            PokemonType::createFromPokemonType($tempEvoOverride->typeOverride1),
            $secondaryType
        );
        if (isset($tempEvoOverride->stats->baseStamina)) {
            assert($tempEvoOverride->stats instanceof stdClass);
            $temporaryEvolution->stats = new PokemonStats(
                $tempEvoOverride->stats->baseStamina,
                $tempEvoOverride->stats->baseAttack,
                $tempEvoOverride->stats->baseDefense,
            );
        }

        return $temporaryEvolution;
    }

    public function getStats(): ?PokemonStats
    {
        return $this->stats;
    }

    public function getTypeSecondary(): PokemonType
    {
        return $this->typeSecondary;
    }

    public function getTypePrimary(): PokemonType
    {
        return $this->typePrimary;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAssetsBundleId(): ?int
    {
        return $this->assetsBundleId;
    }

    public function getAssetsAddressableSuffix(): ?string
    {
        if (strpos($this->id, '_X') !== false) {
            return 'MEGA_X';
        }

        if (strpos($this->id, '_Y') !== false) {
            return 'MEGA_Y';
        }

        return 'MEGA';
    }

    public function setAssetBundleId(int $assetBundleValue): void
    {
        $this->assetsBundleId = $assetBundleValue;
    }
}
