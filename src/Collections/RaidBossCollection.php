<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function array_key_exists;
use function array_values;
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

        $this->storage[$raidBoss->getPokemonId()] = $raidBoss;
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

        return $this->storage[$raidBoss->getPokemonId()];
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
        return array_key_exists($raidBoss->getPokemonId(), $this->storage);
    }

    /** @return RaidBoss[] */
    public function toArray(): array
    {
        $raidBossLevelMapping = [
            RaidBoss::RAID_LEVEL_EX => 10,
            RaidBoss::RAID_LEVEL_MEGA => 8,
            RaidBoss::RAID_LEVEL_5 => 5,
            RaidBoss::RAID_LEVEL_3 => 3,
            RaidBoss::RAID_LEVEL_1 => 1,
        ];
        uasort(
            $this->storage,
            static function (RaidBoss $a, RaidBoss $b) use ($raidBossLevelMapping): int {
                $lvlSort = $raidBossLevelMapping[$b->getRaidLevel()] <=> $raidBossLevelMapping[$a->getRaidLevel()];
                if ($lvlSort !== 0) {
                    return $lvlSort;
                }

                return $a->getPokemon()->getDexNr() <=> $b->getPokemon()->getDexNr();
            }
        );

        return array_values($this->storage);
    }
}
