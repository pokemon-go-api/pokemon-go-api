<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function array_filter;
use function array_key_exists;
use function array_values;
use function implode;
use function sha1;
use function uasort;

final class RaidBossCollection
{
    /** @var array<string, RaidBoss> */
    private array $storage = [];

    public function add(RaidBoss $raidBoss): void
    {
        if ($this->has($raidBoss)) {
            return;
        }

        $this->storage[$this->createRaidBossKey($raidBoss)] = $raidBoss;
    }

    /** @internal */
    public function getById(string $id): RaidBoss|null
    {
        if (! array_key_exists($id, $this->storage)) {
            return null;
        }

        return $this->storage[$id];
    }

    public function get(RaidBoss $raidBoss): RaidBoss|null
    {
        if (! $this->has($raidBoss)) {
            return null;
        }

        return $this->storage[$this->createRaidBossKey($raidBoss)];
    }

    public function remove(string $pokemonId): void
    {
        if (! array_key_exists($pokemonId, $this->storage)) {
            return;
        }

        unset($this->storage[$pokemonId]);
    }

    public function has(RaidBoss $raidBoss): bool
    {
        return array_key_exists($this->createRaidBossKey($raidBoss), $this->storage);
    }

    /** @return RaidBoss[] */
    public function toArray(): array
    {
        uasort(
            $this->storage,
            static function (RaidBoss $a, RaidBoss $b): int {
                $lvlSort = $b->getRaidLevel()->getSortNr() <=> $a->getRaidLevel()->getSortNr();
                if ($lvlSort !== 0) {
                    return $lvlSort;
                }

                $sortByDex = $a->getPokemon()->getDexNr() <=> $b->getPokemon()->getDexNr();
                if ($sortByDex !== 0) {
                    return $sortByDex;
                }

                $aPokemonImage = $a->getPokemonImage();
                $bPokemonImage = $b->getPokemonImage();

                $aImageUrl = $aPokemonImage instanceof PokemonImage ? $aPokemonImage->buildUrl(false) : '';
                $bImageUrl = $bPokemonImage instanceof PokemonImage ? $bPokemonImage->buildUrl(false) : '';

                return $aImageUrl <=> $bImageUrl;
            },
        );

        return array_values($this->storage);
    }

    private function createRaidBossKey(RaidBoss $raidBoss): string
    {
        $keyParts     = [];
        $pokemonImage = $raidBoss->getPokemonImage();
        if ($pokemonImage instanceof PokemonImage) {
            $keyParts[] = sha1($pokemonImage->buildUrl(false));
        } else {
            $keyParts[] = $raidBoss->getPokemonWithMegaFormId();
            $keyParts[] = $raidBoss->getPokemon()->getAssetBundleSuffix();
        }

        $keyParts[] = $raidBoss->getRaidLevel()->value;

        return implode('-', array_filter($keyParts));
    }
}
