<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Tests\Infrastructure\Service\Precondition;

use PhpTuf\ComposerStager\Domain\Service\Precondition\ActiveAndStagingDirsAreDifferentInterface;
use PhpTuf\ComposerStager\Domain\Service\Precondition\ActiveDirIsReadyInterface;
use PhpTuf\ComposerStager\Domain\Service\Precondition\ComposerIsAvailableInterface;
use PhpTuf\ComposerStager\Infrastructure\Service\Precondition\CommonPreconditions;
use PhpTuf\ComposerStager\Tests\Infrastructure\Factory\Translation\TestTranslatableFactory;

/**
 * @coversDefaultClass \PhpTuf\ComposerStager\Infrastructure\Service\Precondition\CommonPreconditions
 *
 * @covers ::__construct
 * @covers ::assertIsFulfilled
 * @covers ::getFulfilledStatusMessage
 * @covers ::getStatusMessage
 * @covers ::isFulfilled
 *
 * @uses \PhpTuf\ComposerStager\Domain\Exception\PreconditionException
 * @uses \PhpTuf\ComposerStager\Infrastructure\Service\Precondition\AbstractPreconditionsTree
 *
 * @property \PhpTuf\ComposerStager\Domain\Service\Precondition\ActiveAndStagingDirsAreDifferentInterface|\Prophecy\Prophecy\ObjectProphecy $activeAndStagingDirsAreDifferent
 * @property \PhpTuf\ComposerStager\Domain\Service\Precondition\ActiveDirIsReadyInterface|\Prophecy\Prophecy\ObjectProphecy $activeDirIsReady
 * @property \PhpTuf\ComposerStager\Domain\Service\Precondition\ComposerIsAvailableInterface|\Prophecy\Prophecy\ObjectProphecy $composerIsAvailable
 */
final class CommonPreconditionsUnitTest extends PreconditionTestCase
{
    protected function setUp(): void
    {
        $this->activeAndStagingDirsAreDifferent = $this->prophesize(ActiveAndStagingDirsAreDifferentInterface::class);
        $this->activeAndStagingDirsAreDifferent
            ->getLeaves()
            ->willReturn([$this->activeAndStagingDirsAreDifferent]);
        $this->activeDirIsReady = $this->prophesize(ActiveDirIsReadyInterface::class);
        $this->activeDirIsReady
            ->getLeaves()
            ->willReturn([$this->activeDirIsReady]);
        $this->composerIsAvailable = $this->prophesize(ComposerIsAvailableInterface::class);
        $this->composerIsAvailable
            ->getLeaves()
            ->willReturn([$this->composerIsAvailable]);

        parent::setUp();
    }

    protected function createSut(): CommonPreconditions
    {
        $activeAndStagingDirsAreDifferent = $this->activeAndStagingDirsAreDifferent->reveal();
        $activeDirIsReady = $this->activeDirIsReady->reveal();
        $composerIsAvailable = $this->composerIsAvailable->reveal();
        $translatableFactory = new TestTranslatableFactory();

        return new CommonPreconditions(
            $activeAndStagingDirsAreDifferent,
            $activeDirIsReady,
            $composerIsAvailable,
            $translatableFactory,
        );
    }

    public function testFulfilled(): void
    {
        $this->composerIsAvailable
            ->assertIsFulfilled($this->activeDir, $this->stagingDir, $this->exclusions)
            ->shouldBeCalledTimes(self::EXPECTED_CALLS_MULTIPLE);
        $this->activeDirIsReady
            ->assertIsFulfilled($this->activeDir, $this->stagingDir, $this->exclusions)
            ->shouldBeCalledTimes(self::EXPECTED_CALLS_MULTIPLE);
        $this->activeAndStagingDirsAreDifferent
            ->assertIsFulfilled($this->activeDir, $this->stagingDir, $this->exclusions)
            ->shouldBeCalledTimes(self::EXPECTED_CALLS_MULTIPLE);

        $this->doTestFulfilled('The common preconditions are fulfilled.');
    }

    public function testUnfulfilled(): void
    {
        $previous = self::createTestPreconditionException(__METHOD__);
        $this->activeAndStagingDirsAreDifferent
            ->assertIsFulfilled($this->activeDir, $this->stagingDir, $this->exclusions)
            ->willThrow($previous);

        $this->doTestUnfulfilled($previous->getMessage());
    }
}
