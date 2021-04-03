<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\IO;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\IO\File;

use function random_bytes;
use function sys_get_temp_dir;
use function unlink;

/**
 * @covers \PokemonGoLingen\PogoAPI\IO\File
 */
class FileTest extends TestCase
{
    public function testGetContent(): void
    {
        $data = random_bytes(32);
        $sut  = new File($data);
        self::assertSame($data, $sut->getContent());
        $tmpFile = sys_get_temp_dir() . '/testfile.dat';

        try {
            $sut->saveTo($tmpFile);
            self::assertStringEqualsFile($tmpFile, $data);
        } finally {
            @unlink($tmpFile);
        }
    }
}
