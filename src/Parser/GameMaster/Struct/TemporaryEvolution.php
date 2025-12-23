<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use PokemonGoApi\PogoAPI\Types\PokemonType;

use function str_contains;
use function substr;

final class TemporaryEvolution
{
    private string $pokemonId = '';
    private readonly string $tempEvoId;
    private readonly PokemonType $typePrimary;
    private readonly PokemonType $typeSecondary;
    private int|null $assetsBundleId = null;

    public function __construct(
        string $tempEvoId,
        private readonly PokemonStats $stats,
        string $typeOverride1,
        string|null $typeOverride2,
    ) {
        $this->tempEvoId   = $tempEvoId;
        $this->typePrimary = PokemonType::createFromPokemonType($typeOverride1);
        if ($typeOverride2 !== null) {
            $this->typeSecondary = PokemonType::createFromPokemonType($typeOverride2);
        } else {
            $this->typeSecondary = PokemonType::none();
        }
    }

    public function setPokemonId(string $pokemonId): void
    {
        $this->pokemonId = $pokemonId;
    }

    public function getStats(): PokemonStats
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
        return $this->pokemonId . substr($this->tempEvoId, 14) ?: ''; // trim TEMP_EVOLUTION
    }

    public function getTempEvoId(): string
    {
        return $this->tempEvoId;
    }

    public function getAssetsBundleId(): int|null
    {
        return $this->assetsBundleId;
    }

    public function getAssetsAddressableSuffix(): string
    {
        if (str_contains($this->tempEvoId, '_X')) {
            return 'MEGA_X';
        }

        if (str_contains($this->tempEvoId, '_Y')) {
            return 'MEGA_Y';
        }

        return 'MEGA';
    }

    public function setAssetBundleId(int $assetBundleValue): void
    {
        $this->assetsBundleId = $assetBundleValue;
    }
}
