<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;

use function strval;

final readonly class Item
{
    public function __construct(
        public string $templateId,
        public string $id,
    ) {
    }

    /** @param array{ templateId: string|int, itemSettings?: array{itemId: string|int}, itemExpirationSettings?: array{item: string|int} } $data */
    #[Constructor]
    public static function fromArray(
        array $data,
    ): self {
        return new self(
            (string) $data['templateId'],
            strval($data['itemSettings']['itemId'] ?? $data['itemExpirationSettings']['item'] ?? $data['templateId']),
        );
    }
}
