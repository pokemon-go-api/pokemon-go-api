<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Util;

final class GenerationDeterminer
{
    public static function fromDexNr(int $dexNr): int
    {
        $generation = 1;
        if ($dexNr > 151) {
            $generation++;
        }

        if ($dexNr > 251) {
            $generation++;
        }

        if ($dexNr > 386) {
            $generation++;
        }

        if ($dexNr > 493) {
            $generation++;
        }

        if ($dexNr > 649) {
            $generation++;
        }

        if ($dexNr > 721) {
            $generation++;
        }

        if ($dexNr > 809) {
            $generation++;
        }

        if ($dexNr > 898) {
            $generation++;
        }

        return $generation;
    }
}
