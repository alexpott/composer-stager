<?php

namespace PhpTuf\ComposerStager\Tests\Exception;

use PhpTuf\ComposerStager\Exception\DirectoryAlreadyExistsException;
use PhpTuf\ComposerStager\Exception\DirectoryNotFoundException;
use PhpTuf\ComposerStager\Exception\DirectoryNotWritableException;
use PhpTuf\ComposerStager\Exception\FileNotFoundException;
use PhpTuf\ComposerStager\Exception\PathException;
use PHPUnit\Framework\TestCase;

class PathExceptionsTest extends TestCase
{
    /**
     * @covers \PhpTuf\ComposerStager\Exception\DirectoryAlreadyExistsException
     * @covers \PhpTuf\ComposerStager\Exception\DirectoryNotFoundException
     * @covers \PhpTuf\ComposerStager\Exception\DirectoryNotWritableException
     * @covers \PhpTuf\ComposerStager\Exception\FileNotFoundException
     * @covers \PhpTuf\ComposerStager\Exception\PathException
     *
     * @dataProvider provider
     */
    public function test(
        $args,
        $path,
        $expectedPathMessage,
        $expectedDirectoryAlreadyExistsMessage,
        $expectedDirectoryNotFoundMessage,
        $expectedDirectoryNotWritableMessage,
        $expectedFileNotFoundMessage
    ): void {
        $pathException = new PathException(...$args);
        $directoryAlreadyExistsException = new DirectoryAlreadyExistsException(...$args);
        $directoryNotFoundException = new DirectoryNotFoundException(...$args);
        $directoryNotWritableException = new DirectoryNotWritableException(...$args);
        $fileNotFoundException = new FileNotFoundException(...$args);

        self::assertSame($expectedPathMessage, $pathException->getMessage());
        self::assertSame($path, $pathException->getPath());
        self::assertSame($expectedDirectoryAlreadyExistsMessage, $directoryAlreadyExistsException->getMessage());
        self::assertSame($expectedDirectoryNotFoundMessage, $directoryNotFoundException->getMessage());
        self::assertSame($expectedDirectoryNotWritableMessage, $directoryNotWritableException->getMessage());
        self::assertSame($expectedFileNotFoundMessage, $fileNotFoundException->getMessage());
    }

    public function provider(): array
    {
        return [
            // Defaults.
            [
                'args' => ['/lorem'],
                'path' => '/lorem',
                'expectedPathMessage' => '',
                'expectedDirectoryAlreadyExistsMessage' => 'Directory already exists: "/lorem"',
                'expectedDirectoryNotFoundMessage' => 'No such directory: "/lorem"',
                'expectedDirectoryNotWritableMessage' => 'Directory not writable: "/lorem"',
                'expectedFileNotFoundMessage' => 'No such file: "/lorem"',
            ],
            // Completely override message.
            [
                'args' => ['/ipsum', 'Lorem ipsum'],
                'path' => '/ipsum',
                'expectedPathMessage' => 'Lorem ipsum',
                'expectedDirectoryAlreadyExistsMessage' => 'Lorem ipsum',
                'expectedDirectoryNotFound' => 'Lorem ipsum',
                'expectedDirectoryNotWritableMessage' => 'Lorem ipsum',
                'expectedFileNotFound' => 'Lorem ipsum',
            ],
            // Override message with path substitution.
            [
                'args' => ['/dolor/sit', 'Lorem ipsum: "%s"'],
                'path' => '/dolor/sit',
                'expectedDirectoryAlreadyExistsMessage' => 'Lorem ipsum: "/dolor/sit"',
                'expectedPathMessage' => 'Lorem ipsum: "/dolor/sit"',
                'expectedDirectoryNotFound' => 'Lorem ipsum: "/dolor/sit"',
                'expectedDirectoryNotWritableMessage' => 'Lorem ipsum: "/dolor/sit"',
                'expectedFileNotFound' => 'Lorem ipsum: "/dolor/sit"',
            ],
        ];
    }
}
