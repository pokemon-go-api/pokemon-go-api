<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

final class PokemonForm
{
    private string $id;
    private ?int $assetBundleValue;
    private ?string $assetBundleSuffix;
    private string $formOnlyId;
    private bool $isCostume;

    public function __construct(
        string $id,
        string $formOnlyId,
        ?int $assetBundleValue,
        ?string $assetBundleSuffix,
        bool $isCostume
    )
    {
        $this->id                = $id;
        $this->assetBundleValue  = $assetBundleValue;
        $this->assetBundleSuffix = $assetBundleSuffix;
        $this->formOnlyId        = $formOnlyId;
        $this->isCostume = $isCostume;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFormOnlyId(): string
    {
        return $this->formOnlyId;
    }

    public function getAssetBundleValue(): int
    {
        return $this->assetBundleValue ?? 0;
    }

    public function getAssetBundleSuffix(): ?string
    {
        return $this->assetBundleSuffix;
    }

    public function isCostume(): bool
    {
        return $this->isCostume;
    }

    public function isAlola(): bool
    {
        return $this->formOnlyId === 'ALOLA';
    }

    public function isGalarian(): bool
    {
        return $this->formOnlyId === 'GALARIAN';
    }
}
