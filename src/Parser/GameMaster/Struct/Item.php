<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

final readonly class Item
{
    public string $templateId;
    public string $id;

    /**
     * @param array{
     *     templateId: string,
     *     itemSettings?: array{itemId: string},
     *     itemExpirationSettings?: array{item: string}
     * } $data
     */
    public function __construct(
        array $data,
    ) {
        $this->templateId = $data['templateId'];
        $this->id         = $data['itemSettings']['itemId'] ?? $data['itemExpirationSettings']['item'] ?? $this->templateId;
    }
}
