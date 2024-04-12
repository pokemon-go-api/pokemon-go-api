<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\IO;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;

use function sleep;
use function sprintf;

class RemoteFileLoader
{
    private readonly ClientInterface $client;

    public function __construct(private readonly LoggerInterface $logger, ClientInterface|null $client = null)
    {
        $this->client = $client ?? new Client();
    }

    public function receiveResponseHeader(string $url, string $headerName): string|null
    {
        $headerResponse = null;
        try {
            $this->logger->debug(sprintf('[RemoteFileLoader] HEAD %s', $url), ['ReceiveHeader' => $headerName]);
            $headerResponse = $this->client->request('HEAD', $url)->getHeaderLine($headerName);
        } catch (ConnectException) {
        }

        return $headerResponse === '' ? null : $headerResponse;
    }

    public function load(string $url): File
    {
        try {
            $this->logger->debug(sprintf('[RemoteFileLoader] GET %s', $url));
            $content = $this->client->request('GET', $url, ['timeout' => 20])->getBody()->getContents();
        } catch (ConnectException) {
            sleep(5);
            $content = $this->client->request('GET', $url, ['timeout' => 5])->getBody()->getContents();
        }

        return new File($content);
    }
}
