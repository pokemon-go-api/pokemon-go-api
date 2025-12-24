<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;

final readonly class Item
{
    public function __construct(
        public string $templateId,
        public string $id,
    ) {
    }

    /** @param array{ templateId: string, itemSettings?: array{itemId: string}, itemExpirationSettings?: array{item: string} } $data */
    #[Constructor]
    public static function fromArray(
        array $data,
    ): self {
        return new self(
            $data['templateId'],
            $data['itemSettings']['itemId'] ?? $data['itemExpirationSettings']['item'] ?? $data['templateId'],
        );
    }
}
