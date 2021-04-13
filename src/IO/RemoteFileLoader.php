<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\IO;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;

use function sleep;
use function sprintf;

class RemoteFileLoader
{
    private ClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, ?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client();
        $this->logger = $logger;
    }

    public function receiveResponseHeader(string $url, string $headerName): ?string
    {
        $headerResponse = null;
        try {
            $this->logger->debug(sprintf('[RemoteFileLoader] HEAD %s', $url), ['ReceiveHeader' => $headerName]);
            $headerResponse = $this->client->request('HEAD', $url)->getHeaderLine($headerName);
        } catch (ConnectException $connectException) {
        }

        return $headerResponse === '' ? null : $headerResponse;
    }

    public function load(string $url): File
    {
        try {
            $this->logger->debug(sprintf('[RemoteFileLoader] GET %s', $url));
            $content = $this->client->request('GET', $url)->getBody()->getContents();
        } catch (ConnectException $connectException) {
            sleep(5);
            $content = $this->client->request('GET', $url)->getBody()->getContents();
        }

        return new File($content);
    }
}
