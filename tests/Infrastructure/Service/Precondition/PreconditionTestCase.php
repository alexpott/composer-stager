<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Tests\Infrastructure\Service\Precondition;

use PhpTuf\ComposerStager\Domain\Exception\PreconditionException;
use PhpTuf\ComposerStager\Domain\Service\Precondition\PreconditionInterface;
use PhpTuf\ComposerStager\Domain\Value\Translation\TranslatableInterface;
use PhpTuf\ComposerStager\Tests\Infrastructure\Value\Path\TestPath;
use PhpTuf\ComposerStager\Tests\Infrastructure\Value\Path\TestPathList;
use PhpTuf\ComposerStager\Tests\TestCase;

/**
 * @property \PhpTuf\ComposerStager\Domain\Value\Path\PathListInterface $exclusions
 * @property \PhpTuf\ComposerStager\Tests\Infrastructure\Value\Path\TestPath $activeDir
 * @property \PhpTuf\ComposerStager\Tests\Infrastructure\Value\Path\TestPath $stagingDir
 */
abstract class PreconditionTestCase extends TestCase
{
    // Multiply expected calls to prophecies to account for multiple calls to ::isFulfilled()
    // and assertIsFulfilled() in ::doTestFulfilled() and ::doTestUnfulfilled(), respectively.
    protected const EXPECTED_CALLS_MULTIPLE = 3;

    protected function setUp(): void
    {
        $this->activeDir = new TestPath(self::ACTIVE_DIR);
        $this->stagingDir = new TestPath(self::STAGING_DIR);
        $this->exclusions = new TestPathList();
    }

    abstract protected function createSut(): PreconditionInterface;

    /**
     * @covers ::__construct
     * @covers ::getDescription
     * @covers ::getLeaves
     * @covers ::getName
     *
     * @uses \PhpTuf\ComposerStager\Infrastructure\Value\Translation\TranslationParameters
     */
    public function testGetters(): void
    {
        $sut = $this->createSut();

        self::assertInstanceOf(TranslatableInterface::class, $sut->getName());
        self::assertInstanceOf(TranslatableInterface::class, $sut->getDescription());
        self::assertIsArray($sut->getLeaves());
    }

    protected function doTestFulfilled(string $expectedStatusMessage): void
    {
        $sut = $this->createSut();

        $isFulfilled = $sut->isFulfilled($this->activeDir, $this->stagingDir, $this->exclusions);
        $actualStatusMessage = $sut->getStatusMessage($this->activeDir, $this->stagingDir, $this->exclusions);
        $sut->assertIsFulfilled($this->activeDir, $this->stagingDir, $this->exclusions);

        self::assertTrue($isFulfilled);
        self::assertTranslatableMessage($expectedStatusMessage, $actualStatusMessage, 'Got correct status message.');
    }

    protected function doTestUnfulfilled(string $expectedStatusMessage, ?string $previousException = null): void
    {
        $sut = $this->createSut();

        self::assertTranslatableMessage($expectedStatusMessage, $sut->getStatusMessage($this->activeDir, $this->stagingDir, $this->exclusions));
        self::assertTranslatableException(function () use ($sut) {
            $sut->assertIsFulfilled($this->activeDir, $this->stagingDir, $this->exclusions);
        }, PreconditionException::class, $expectedStatusMessage, $previousException);
    }
}
