<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

final class PokemonForm
{
    private string $id;
    private int $assetBundleValue;
    private string $formOnlyId;

    public function __construct(string $id, string $formOnlyId, int $assetBundleValue)
    {
        $this->id               = $id;
        $this->assetBundleValue = $assetBundleValue;
        $this->formOnlyId       = $formOnlyId;
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
        return $this->assetBundleValue;
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
