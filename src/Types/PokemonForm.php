<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

final readonly class PokemonForm
{
    public function __construct(
        private string $id,
        private string $formOnlyId,
        private bool $isCostume,
        private int|null $assetBundleValue,
        private string|null $assetBundleSuffix,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isCostume(): bool
    {
        return $this->isCostume;
    }

    public function getFormOnlyId(): string
    {
        return $this->formOnlyId;
    }

    public function getAssetBundleValue(): int
    {
        return $this->assetBundleValue ?? 0;
    }

    public function getAssetBundleSuffix(): string|null
    {
        return $this->assetBundleSuffix;
    }

    public function isAlola(): bool
    {
        return $this->formOnlyId === 'ALOLA';
    }

    public function isGalarian(): bool
    {
        return $this->formOnlyId === 'GALARIAN';
    }

    public function isHisuian(): bool
    {
        return $this->formOnlyId === 'HISUIAN';
    }
}
