<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

final class PokemonType
{
    private string $typeName;

    public function __construct(string $typeName)
    {
        $this->typeName = $typeName;
    }

    public static function create(string $typeName): self
    {
        return new self($typeName);
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function __toString(): string
    {
        return $this->typeName;
    }
}
