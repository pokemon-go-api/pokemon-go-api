<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;
use PokemonGoApi\PogoAPI\Types\PokemonType;

use function str_contains;
use function substr;

final class TemporaryEvolution
{
    public function __construct(
        private readonly string $tempEvoId,
        private readonly PokemonStats $stats,
        private readonly PokemonType $typePrimary,
        private readonly PokemonType $typeSecondary,
        private string $pokemonId = '',
        private int|null $assetsBundleId = null,
    ) {
    }

    #[Constructor]
    public static function fromArray(
        string $tempEvoId,
        PokemonStats $stats,
        string $typeOverride1,
        string|null $typeOverride2,
    ): self {
        $typeSecondary = PokemonType::none();
        if ($typeOverride2 !== null) {
            $typeSecondary = PokemonType::createFromPokemonType($typeOverride2);
        }

        return new self(
            $tempEvoId,
            $stats,
            PokemonType::createFromPokemonType($typeOverride1),
            $typeSecondary,
            '',
        );
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
