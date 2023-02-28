<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;
use stdClass;

use function assert;
use function strpos;
use function substr;

final class TemporaryEvolution
{
    private int|null $assetsBundleId = null;
    private PokemonType $typeSecondary;
    private PokemonStats|null $stats = null;

    public function __construct(private string $id, private PokemonType $typePrimary, PokemonType|null $typeSecondary)
    {
        $this->typeSecondary = $typeSecondary ?? PokemonType::none();
    }

    public static function createFromGameMaster(object $tempEvoOverride, string $basePokemonId): self
    {
        if (! isset($tempEvoOverride->tempEvoId, $tempEvoOverride->typeOverride1, $tempEvoOverride->stats)) {
            throw new Exception('Invalid tempEvoOverride given', 1661601184);
        }

        $secondaryType = null;
        if (isset($tempEvoOverride->typeOverride2)) {
            $secondaryType = PokemonType::createFromPokemonType($tempEvoOverride->typeOverride2);
        }

        $temporaryEvolution = new self(
            $basePokemonId . substr($tempEvoOverride->tempEvoId, 14), // trim TEMP_EVOLUTION
            PokemonType::createFromPokemonType($tempEvoOverride->typeOverride1),
            $secondaryType,
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

    public function getStats(): PokemonStats|null
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

    public function getAssetsBundleId(): int|null
    {
        return $this->assetsBundleId;
    }

    public function getAssetsAddressableSuffix(): string|null
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
