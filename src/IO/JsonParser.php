<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\IO;

use stdClass;

use function json_decode;

use const JSON_THROW_ON_ERROR;

final class JsonParser
{
    /**
     * @return array<mixed, mixed>
     */
    public static function decodeToArray(string $data): array
    {
        return (array) (json_decode($data, false, 512, JSON_THROW_ON_ERROR) ?: []);
    }

    public static function decodeToObject(string $data): stdClass
    {
        return (object) self::decodeToArray($data);
    }
}
