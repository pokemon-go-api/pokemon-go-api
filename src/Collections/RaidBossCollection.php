<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function array_key_exists;
use function array_values;
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

    public function getById(string $id): ?RaidBoss
    {
        if (! array_key_exists($id, $this->storage)) {
            return null;
        }

        return $this->storage[$id];
    }

    public function get(RaidBoss $raidBoss): ?RaidBoss
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
        $raidBossLevelMapping = [
            RaidBoss::RAID_LEVEL_EX             => 12,
            RaidBoss::RAID_LEVEL_LEGENDARY_MEGA => 10,
            RaidBoss::RAID_LEVEL_MEGA           => 8,
            RaidBoss::RAID_LEVEL_5              => 5,
            RaidBoss::RAID_LEVEL_3              => 3,
            RaidBoss::RAID_LEVEL_1              => 1,
        ];
        uasort(
            $this->storage,
            static function (RaidBoss $a, RaidBoss $b) use ($raidBossLevelMapping): int {
                $lvlSort = $raidBossLevelMapping[$b->getRaidLevel()] <=> $raidBossLevelMapping[$a->getRaidLevel()];
                if ($lvlSort !== 0) {
                    return $lvlSort;
                }

                $sortByDex = $a->getPokemon()->getDexNr() <=> $b->getPokemon()->getDexNr();
                if ($sortByDex !== 0) {
                    return $sortByDex;
                }

                $aPokemonImage = $a->getPokemonImage();
                $bPokemonImage = $b->getPokemonImage();

                $aImageUrl = $aPokemonImage ? $aPokemonImage->buildUrl(false) : '';
                $bImageUrl = $bPokemonImage ? $bPokemonImage->buildUrl(false) : '';

                return $aImageUrl <=> $bImageUrl;
            }
        );

        return array_values($this->storage);
    }

    private function createRaidBossKey(RaidBoss $raidBoss): string
    {
        $pokemonImage = $raidBoss->getPokemonImage();
        if ($pokemonImage === null) {
            return $raidBoss->getPokemonWithMegaFormId();
        }

        return sha1($pokemonImage->buildUrl(false));
    }
}
