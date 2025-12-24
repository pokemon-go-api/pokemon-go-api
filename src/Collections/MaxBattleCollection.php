<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Types\MaxBattle;

use function array_filter;
use function array_key_exists;
use function array_values;
use function implode;
use function uasort;

final class MaxBattleCollection
{
    /** @var array<string, MaxBattle> */
    private array $storage = [];

    public function add(MaxBattle $maxBattle): void
    {
        if ($this->has($maxBattle)) {
            return;
        }

        $this->storage[$this->createIdKey($maxBattle)] = $maxBattle;
    }

    public function get(MaxBattle $maxBattle): MaxBattle|null
    {
        if (! $this->has($maxBattle)) {
            return null;
        }

        return $this->storage[$this->createIdKey($maxBattle)];
    }

    public function remove(string $pokemonId): void
    {
        if (! array_key_exists($pokemonId, $this->storage)) {
            return;
        }

        unset($this->storage[$pokemonId]);
    }

    public function has(MaxBattle $maxBattle): bool
    {
        return array_key_exists($this->createIdKey($maxBattle), $this->storage);
    }

    /** @return MaxBattle[] */
    public function toArray(): array
    {
        uasort(
            $this->storage,
            static function (MaxBattle $a, MaxBattle $b): int {
                $lvlSort = $a->maxBattleLevel->value <=> $b->maxBattleLevel->value;
                if ($lvlSort !== 0) {
                    return $lvlSort;
                }

                return $a->pokemon->getDexNr() <=> $b->pokemon->getDexNr();
            },
        );

        return array_values($this->storage);
    }

    private function createIdKey(MaxBattle $maxBattle): string
    {
        $keyParts   = [];
        $keyParts[] = $maxBattle->pokemon->getDexNr();
        $keyParts[] = $maxBattle->maxBattleLevel->value;

        return implode('-', array_filter($keyParts));
    }
}
