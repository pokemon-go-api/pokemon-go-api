<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\IO;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;

use function sleep;

class RemoteFileLoader
{
    private ClientInterface $client;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client();
    }

    public function load(string $url): File
    {
        try {
            $content = $this->client->request('GET', $url)->getBody()->getContents();
        } catch (ConnectException $connectException) {
            sleep(5);
            $content = $this->client->request('GET', $url)->getBody()->getContents();
        }

        return new File($content);
    }
}
