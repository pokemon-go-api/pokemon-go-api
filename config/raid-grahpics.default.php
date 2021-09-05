<?php

use PokemonGoApi\PogoAPI\Renderer\Types\RaidBossGraphicConfig;

return [
    'raid-graphics' => [
        'default' => new RaidBossGraphicConfig(),
        'reverse' => new RaidBossGraphicConfig(RaidBossGraphicConfig::ORDER_LVL1_TO_MEGA, false),
    ]
];