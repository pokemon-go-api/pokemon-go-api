<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

enum RaidLevel: string
{
    case ShadowRaid1  = 'shadow_lvl1';
    case  ShadowRaid3 = 'shadow_lvl3';
    case  ShadowRaid5 = 'shadow_lvl5';

    case Raid1             = 'lvl1';
    case Raid3             = 'lvl3';
    case Raid5             = 'lvl5';
    case RaidEx            = 'ex';
    case RaidMega          = 'mega';
    case RaidLegendaryMega = 'legendary_mega';
    case RaidUltraBeast    = 'ultra_beast';

    public function isShadow(): bool
    {
        return match ($this) {
            self::ShadowRaid1,
            self::ShadowRaid3,
            self::ShadowRaid5 => true,
            default => false,
        };
    }

    public function getSortNr(): int
    {
        return match ($this) {
            self::RaidUltraBeast => 20,
            self::RaidLegendaryMega => 15,
            self::RaidEx => 14,
            self::RaidMega => 13,
            self::Raid5 => 10,
            self::Raid3 => 9,
            self::Raid1 => 8,
            self::ShadowRaid5 => 5,
            self::ShadowRaid3 => 4,
            self::ShadowRaid1 => 3,
        };
    }

    public function toPokebattlerLevel(): string
    {
        return match ($this) {
            self::Raid1 => '1',
            self::ShadowRaid1 => '1_SHADOW',
            self::Raid3 => '3',
            self::ShadowRaid3 => '3_SHADOW',
            self::Raid5 => '5',
            self::ShadowRaid5 => '5_SHADOW',
            self::RaidEx => '6',
            self::RaidMega => 'MEGA',
            self::RaidLegendaryMega => 'MEGA_5',
            self::RaidUltraBeast => 'ULTRA_BEAST',
        };
    }

    public function getEggIconName(): string
    {
        return match ($this) {
            self::RaidUltraBeast => 'ultra',
            self::RaidEx => 'egg_2',
            self::RaidMega => 'egg_3',
            self::RaidLegendaryMega => 'egg_4',
            self::Raid5, self::ShadowRaid5 => 'egg_2',
            self::Raid3, self::ShadowRaid3 => 'egg_1',
            self::Raid1, self::ShadowRaid1 => 'egg_0',
        };
    }
}
