<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\IO;

use PokemonGoApi\PogoAPI\IO\Struct\GithubFileResponse;
use stdClass;

use function array_map;
use function assert;
use function basename;
use function explode;
use function shell_exec;
use function sprintf;

class GithubLoader
{
    private const AVAILABLE_GAMEMASTER_REPOS = [
        ['owner' => 'alexelgt', 'repo' => 'game_masters', 'path' => 'GAME_MASTER.json'],
        ['owner' => 'PokeMiners', 'repo' => 'game_masters', 'path' => 'latest/latest.json'],
    ];

    private const AVAILABLE_TEXT_REPOS = [
        [
            'owner' => 'sora10pls',
            'repo' => 'holoholo-text',
            'filesRemote' => [
                'English' => 'Remote/English/en-us_formatted.txt',
                'German' => 'Remote/German/de-de_formatted.txt',
                'French' => 'Remote/French/fr-fr_formatted.txt',
                'Italian' => 'Remote/Italian/it-it_formatted.txt',
                'Japanese' => 'Remote/Japanese/ja-jp_formatted.txt',
                'Korean' => 'Remote/Korean/ko-kr_formatted.txt',
                'Spanish' => 'Remote/Spanish/es-es_formatted.txt',
            ],
            'filesApk' => [
                'English' => 'Release/English/en-us_formatted.txt',
                'German' => 'Release/German/de-de_formatted.txt',
                'French' => 'Release/French/fr-fr_formatted.txt',
                'Italian' => 'Release/Italian/it-it_formatted.txt',
                'Japanese' => 'Release/Japanese/ja-jp_formatted.txt',
                'Korean' => 'Release/Korean/ko-kr_formatted.txt',
                'Spanish' => 'Release/Spanish/es-es_formatted.txt',
            ],
        ],
    ];

    public const string ASSETS_BASE_URL = 'https://raw.githubusercontent.com/RetroJohn86/PoGo-Unpacked-DL-Assets/main/Sprite/pm%20and%20portraits/';

    public function __construct(
        private readonly RemoteFileLoader $remoteFileLoader,
    ) {
    }

    public function getLatestGameMasterFile(): GithubFileResponse
    {
        foreach (self::AVAILABLE_GAMEMASTER_REPOS as $gameMasterConfig) {
            $gameMasterFileData = $this->remoteFileLoader->load(sprintf(
                'https://api.github.com/repos/%s/%s/contents/%s',
                $gameMasterConfig['owner'],
                $gameMasterConfig['repo'],
                $gameMasterConfig['path'],
            ))->getContent() ?: '[]';
            $gameMasterFile     = JsonParser::decodeToObject($gameMasterFileData);
            assert($gameMasterFile instanceof stdClass);

            return new GithubFileResponse(
                $gameMasterFile->name,
                $gameMasterFile->path,
                $gameMasterFile->sha,
                //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
                $gameMasterFile->download_url,
            );
        }
    }

    /** @return array{remote: array<string, GithubFileResponse>, apk: array<string, GithubFileResponse>} */
    public function getLatestTextFiles(): array
    {
        $remoteFiles = [];
        $apkFiles    = [];
        foreach (self::AVAILABLE_TEXT_REPOS as $textRepoConfig) {
            foreach ($textRepoConfig['filesRemote'] as $fileAlias => $remoteFile) {
                $textFileData            = $this->remoteFileLoader->load(sprintf(
                    'https://api.github.com/repos/%s/%s/contents/%s',
                    $textRepoConfig['owner'],
                    $textRepoConfig['repo'],
                    $remoteFile,
                ))->getContent() ?: '[]';
                $textFile                = JsonParser::decodeToObject($textFileData);
                $remoteFiles[$fileAlias] = new GithubFileResponse(
                    $textFile->name,
                    $textFile->path,
                    $textFile->sha,
                    //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
                    $textFile->download_url,
                );
            }

            foreach ($textRepoConfig['filesApk'] as $fileAlias => $aplFile) {
                $textFileData         = $this->remoteFileLoader->load(sprintf(
                    'https://api.github.com/repos/%s/%s/contents/%s',
                    $textRepoConfig['owner'],
                    $textRepoConfig['repo'],
                    $aplFile,
                ))->getContent() ?: '[]';
                $textFile             = JsonParser::decodeToObject($textFileData);
                $apkFiles[$fileAlias] = new GithubFileResponse(
                    $textFile->name,
                    $textFile->path,
                    $textFile->sha,
                    //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
                    $textFile->download_url,
                );
            }
        }

        return [
            'remote' => $remoteFiles,
            'apk' => $apkFiles,
        ];
    }

    /** @return list<string> */
    public function getImageList(): array
    {
        shell_exec('rm -rf data/tmp/git-assets && mkdir data/tmp/git-assets');
        //phpcs:ignore Generic.Files.LineLength.TooLong
        shell_exec('git clone -q --filter=blob:none --no-checkout https://github.com/RetroJohn86/PoGo-Unpacked-DL-Assets.git data/tmp/git-assets');
        $files = shell_exec(<<<'SHELL'
        git --git-dir data/tmp/git-assets/.git ls-tree --name-only HEAD 'Sprite/pm and portraits/'
        SHELL);

        $allFiles = explode("\n", (string) $files);

        return array_map(
            static fn (string $file): string => basename($file),
            $allFiles,
        );
    }
}
