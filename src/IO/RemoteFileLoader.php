<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\IO;

use GuzzleHttp\Client;

final class RemoteFileLoader
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function load(string $url): File
    {
        $content = $this->client->request('GET', $url)->getBody()->getContents();

        return new File($content);
    }
}
