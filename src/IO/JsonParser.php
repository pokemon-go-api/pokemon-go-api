<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\IO;

use Exception;
use stdClass;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class JsonParser
{
    /** @return array<mixed> */
    public static function decodeGameMasterFileData(string $jsonContent): array
    {
        $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data) || ! isset($data['data']) || ! is_array($data['data'])) {
            throw new Exception('Invalid GameMaster file', 1766489897097);
        }

        return $data['data'];
    }

    /** @return array<mixed, mixed> */
    public static function decodeToArray(string $data): array
    {
        return (array) (json_decode($data, false, 512, JSON_THROW_ON_ERROR) ?: []);
    }

    /** @return array<mixed, mixed> */
    public static function decodeToFullArray(string $data): array
    {
        return (array) (json_decode($data, true, 512, JSON_THROW_ON_ERROR) ?: []);
    }

    public static function decodeToObject(string $data): stdClass
    {
        return (object) self::decodeToArray($data);
    }
}
