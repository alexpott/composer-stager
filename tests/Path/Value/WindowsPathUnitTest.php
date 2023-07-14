<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Tests\Path\Value;

use PhpTuf\ComposerStager\API\Path\Value\PathInterface;
use PhpTuf\ComposerStager\Internal\Path\Value\WindowsPath;
use PhpTuf\ComposerStager\Tests\TestCase;

/**
 * @coversDefaultClass \PhpTuf\ComposerStager\Internal\Path\Value\WindowsPath
 *
 * @covers ::__construct
 * @covers ::doResolve
 * @covers ::getAbsoluteFromRelative
 * @covers ::isAbsolute
 * @covers ::isAbsoluteFromCurrentDrive
 * @covers ::isAbsoluteFromSpecificDrive
 * @covers ::normalize
 * @covers ::normalizeAbsoluteFromCurrentDrive
 * @covers ::normalizeAbsoluteFromSpecificDrive
 * @covers ::raw
 * @covers ::resolved
 * @covers ::resolvedRelativeTo
 * @covers \PhpTuf\ComposerStager\Internal\Path\Value\AbstractPath::getcwd
 */
final class WindowsPathUnitTest extends TestCase
{
    public string $basePath;

    /** @dataProvider providerBasicFunctionality */
    public function testBasicFunctionality(
        string $given,
        string $basePath,
        bool $isAbsolute,
        string $resolved,
        string $relativeBase,
        string $resolvedRelativeTo,
    ): void {
        self::fixSeparatorsMultiple($given, $basePath, $resolved, $relativeBase, $resolvedRelativeTo);

        $sut = new WindowsPath($given);
        $equalInstance = new WindowsPath($given);
        $unequalInstance = new WindowsPath(__DIR__);
        $relativeBase = new WindowsPath($relativeBase);

        // Dynamically override base path.
        $setBaseDir = function ($basePath): void {
            $this->basePath = $basePath;
        };
        $setBaseDir->call($sut, $basePath);
        $setBaseDir->call($equalInstance, $basePath);

        self::assertEquals($isAbsolute, $sut->isAbsolute(), 'Correctly determined whether given path was relative.');
        self::assertEquals($given, $sut->raw(), 'Correctly returned raw path.');
        self::assertEquals($resolved, $sut->resolved(), 'Correctly resolved path.');
        self::assertEquals($resolvedRelativeTo, $sut->resolvedRelativeTo($relativeBase), 'Correctly resolved path relative to another given path.');
        self::assertEquals($sut, $equalInstance, 'Path value considered equal to another instance with the same input.');
        self::assertNotEquals($sut, $unequalInstance, 'Path value considered unequal to another instance with different input.');

        // Make sure object is truly immutable.
        chdir(__DIR__);
        self::assertEquals($resolved, $sut->resolved(), 'Retained correct value after changing working directory.');
        self::assertEquals($sut, $equalInstance, 'Path value still considered equal to another instance with the same input after changing working directory.');
        self::assertNotEquals($sut, $unequalInstance, 'Path value considered unequal to another instance with different input.');
    }

    public function providerBasicFunctionality(): array
    {
        return [
            // Special base path paths.
            'Path as empty string ()' => [
                'given' => '',
                'baseDir' => 'C:\\Windows\\One',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\One',
                'relativeBase' => 'D:\\Users\\Two',
                'resolvedRelativeTo' => 'D:\\Users\\Two',
            ],
            'Path as dot (.)' => [
                'given' => '.',
                'baseDir' => 'C:\\Windows\\Three',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\Three',
                'relativeBase' => 'D:\\Users\\Four',
                'resolvedRelativeTo' => 'D:\\Users\\Four',
            ],
            // Relative paths.
            'Relative path as simple string' => [
                'given' => 'One',
                'baseDir' => 'C:\\Windows',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\One',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\Users\\One',
            ],
            'Relative path as space ( )' => [
                'given' => ' ',
                'baseDir' => 'C:\\Windows\\Two',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\Two\\ ',
                'relativeBase' => 'D:\\Users\\Three',
                'resolvedRelativeTo' => 'D:\\Users\\Three\\ ',
            ],
            'Relative path with nesting' => [
                'given' => 'One\\Two\\Three\\Four\\Five',
                'baseDir' => 'C:\\Windows',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\One\\Two\\Three\\Four\\Five',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\Users\\One\\Two\\Three\\Four\\Five',
            ],
            'Relative path with trailing slash' => [
                'given' => 'One\\Two\\',
                'baseDir' => 'C:\\Windows',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\One\\Two',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\Users\\One\\Two',
            ],
            'Relative path with repeating directory separators' => [
                'given' => 'One\\\\Two\\\\\\\\Three',
                'baseDir' => 'C:\\Windows\\Four',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\Four\\One\\Two\\Three',
                'relativeBase' => 'D:\\Users\\Five',
                'resolvedRelativeTo' => 'D:\\Users\\Five\\One\\Two\\Three',
            ],
            'Relative path with double dots (..)' => [
                'given' => '..\\One\\..\\Two\\Three\\Four\\..\\..\\Five\\Six\\..',
                'baseDir' => 'C:\\Windows\\Seven\\Eight',
                'isAbsolute' => false,
                'resolved' => 'C:\\Windows\\Seven\\Two\\Five',
                'relativeBase' => 'D:\\Users\\Nine\\Ten',
                'resolvedRelativeTo' => 'D:\\Users\\Nine\\Two\\Five',
            ],
            'Relative path with leading double dots (..) and root path base path' => [
                'given' => '..\\One\\Two',
                'baseDir' => 'C:\\',
                'isAbsolute' => false,
                'resolved' => 'C:\\One\\Two',
                'relativeBase' => 'D:\\',
                'resolvedRelativeTo' => 'D:\\One\\Two',
            ],
            'Silly combination of relative path as double dots (..) with root path base path' => [
                'given' => '..',
                'baseDir' => 'C:\\',
                'isAbsolute' => false,
                'resolved' => 'C:\\',
                'relativeBase' => 'D:\\',
                'resolvedRelativeTo' => 'D:\\',
            ],
            'Crazy relative path' => [
                'given' => 'One\\.\\\\\\\\.\\Two\\Three\\Four\\Five\\.\\.\\.\\..\\\\.\\\\..\\\\\\\\\\..\\.\\.\\..\\.\\Six\\\\\\\\\\',
                'baseDir' => 'C:\\Seven\\Eight\\Nine\\Ten',
                'isAbsolute' => false,
                'resolved' => 'C:\\Seven\\Eight\\Nine\\Ten\\One\\Six',
                'relativeBase' => 'D:\\Eleven\\Twelve\\Thirteen\\Fourteen',
                'resolvedRelativeTo' => 'D:\\Eleven\\Twelve\\Thirteen\\Fourteen\\One\\Six',
            ],
            // Absolute paths from the root of a specific drive.
            'Absolute path to the root of a specific drive' => [
                'given' => 'D:\\',
                'baseDir' => 'C:\\',
                'isAbsolute' => true,
                'resolved' => 'D:\\',
                'relativeBase' => 'D:\\',
                'resolvedRelativeTo' => 'D:\\',
            ],
            'Absolute path from the root of a specific drive as simple string' => [
                'given' => 'D:\\One',
                'baseDir' => 'C:\\Windows',
                'isAbsolute' => true,
                'resolved' => 'D:\\One',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\One',
            ],
            'Absolute path from the root of a specific drive with nesting' => [
                'given' => 'D:\\One\\Two\\Three\\Four\\Five',
                'baseDir' => 'C:\\Windows\\Six\\Seven\\Eight\\Nine',
                'isAbsolute' => true,
                'resolved' => 'D:\\One\\Two\\Three\\Four\\Five',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\One\\Two\\Three\\Four\\Five',
            ],
            'Crazy absolute path from the root of a specific drive' => [
                'given' => 'D:\\One\\.\\\\\\\\.\\Two\\Three\\Four\\Five\\.\\.\\.\\..\\\\.\\\\..\\\\\\\\\\..\\.\\.\\..\\.\\Six\\\\\\\\\\',
                'baseDir' => 'C:\\Seven\\Eight\\Nine\\Ten',
                'isAbsolute' => true,
                'resolved' => 'D:\\One\\Six',
                'relativeBase' => 'D:\\Eleven\\Twelve\\Fourteen',
                'resolvedRelativeTo' => 'D:\\One\\Six',
            ],
            // Absolute paths from the root of the current drive.
            'Absolute path to the root of the current drive' => [
                'given' => '\\',
                'baseDir' => 'C:\\',
                'isAbsolute' => true,
                'resolved' => 'C:\\',
                'relativeBase' => 'C:\\',
                'resolvedRelativeTo' => 'C:\\',
            ],
            'Absolute path from the root of the current drive as a simple string' => [
                'given' => '\\One',
                'baseDir' => 'C:\\Windows',
                'isAbsolute' => true,
                'resolved' => 'C:\\One',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\One',
            ],
            'Absolute path from the root of the current drive with nesting' => [
                'given' => '\\One\\Two\\Three\\Four\\Five',
                'baseDir' => 'C:\\Windows\\Six\\Seven\\Eight\\Nine',
                'isAbsolute' => true,
                'resolved' => 'C:\\One\\Two\\Three\\Four\\Five',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\One\\Two\\Three\\Four\\Five',
            ],
            'Crazy absolute path from the root of the current drive' => [
                'given' => '\\One\\.\\\\\\\\.\\Two\\Three\\Four\\Five\\.\\.\\.\\..\\\\.\\\\..\\\\\\\\\\..\\.\\.\\..\\.\\Six\\\\\\\\\\',
                'baseDir' => 'C:\\Seven\\Eight\\Nine\\Ten',
                'isAbsolute' => true,
                'resolved' => 'C:\\One\\Six',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\One\\Six',
            ],
            'Crazy absolute path from the root of a specified drive' => [
                'given' => '\\One\\.\\\\\\\\.\\Two\\Three\\Four\\Five\\.\\.\\.\\..\\\\.\\\\..\\\\\\\\\\..\\.\\.\\..\\.\\Six\\\\\\\\\\',
                'baseDir' => 'C:\\Seven\\Eight\\Nine\\Ten',
                'isAbsolute' => true,
                'resolved' => 'C:\\One\\Six',
                'relativeBase' => 'D:\\Users',
                'resolvedRelativeTo' => 'D:\\One\\Six',
            ],
        ];
    }

    /**
     * @dataProvider providerBaseDirArgument
     *
     * @group windows_only
     *   This test doesn't work well on non-Windows systems, owing to its dependence on getcwd().
     */
    public function testOptionalBaseDirArgument(string $path, ?PathInterface $basePath, string $resolved): void
    {
        $sut = new WindowsPath($path, $basePath);

        self::assertEquals($resolved, $sut->resolved(), 'Correctly resolved path.');
    }

    public function providerBaseDirArgument(): array
    {
        return [
            'With $basePath argument.' => [
                'path' => 'One',
                'baseDir' => new TestPath('C:\\Arg'),
                'resolved' => 'C:\\Arg\\One',
            ],
            'With explicit null $basePath argument' => [
                'path' => 'One',
                'baseDir' => null,
                'resolved' => sprintf('%s\\One', getcwd()),
            ],
        ];
    }
}
