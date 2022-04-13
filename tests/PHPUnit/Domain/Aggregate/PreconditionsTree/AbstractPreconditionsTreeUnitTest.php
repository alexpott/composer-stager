<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Tests\PHPUnit\Domain\Aggregate\PreconditionsTree;

use PhpTuf\ComposerStager\Domain\Exception\PreconditionException;
use PhpTuf\ComposerStager\Domain\Service\Precondition\PreconditionInterface;
use PhpTuf\ComposerStager\Domain\Value\Path\PathInterface;
use PhpTuf\ComposerStager\Infrastructure\Aggregate\PreconditionsTree\AbstractPreconditionsTree;
use PhpTuf\ComposerStager\Tests\PHPUnit\TestCase;

/**
 * @coversDefaultClass \PhpTuf\ComposerStager\Infrastructure\Aggregate\PreconditionsTree\AbstractPreconditionsTree
 *
 * @uses \PhpTuf\ComposerStager\Infrastructure\Aggregate\PreconditionsTree\AbstractPreconditionsTree::__construct
 *
 * @property \PhpTuf\ComposerStager\Domain\Value\Path\PathInterface|\Prophecy\Prophecy\ObjectProphecy $activeDir
 * @property \PhpTuf\ComposerStager\Domain\Value\Path\PathInterface|\Prophecy\Prophecy\ObjectProphecy $path
 * @property \PhpTuf\ComposerStager\Domain\Value\Path\PathInterface|\Prophecy\Prophecy\ObjectProphecy $stagingDir
 */
final class AbstractPreconditionsTreeUnitTest extends TestCase
{
    protected function setUp(): void
    {
        $this->activeDir = $this->prophesize(PathInterface::class);
        $this->activeDir
            ->resolve()
            ->willReturn(self::ACTIVE_DIR);
        $this->stagingDir = $this->prophesize(PathInterface::class);
        $this->stagingDir
            ->resolve()
            ->willReturn(self::STAGING_DIR);
        $this->path = $this->prophesize(PathInterface::class);
    }

    protected function createSut(...$children): AbstractPreconditionsTree
    {
        // Create a concrete implementation for testing since the SUT, being
        // abstract, can't be instantiated directly.
        return new class (...$children) extends AbstractPreconditionsTree
        {
            public $name = 'Name';
            public $description = 'Description';
            public $isFulfilled = true;
            public $fulfilledStatusMessage = 'Fulfilled';
            public $unfulfilledStatusMessage = 'Unfulfilled';

            public function getName(): string
            {
                return $this->name;
            }

            public function getDescription(): string
            {
                return $this->description;
            }

            protected function getFulfilledStatusMessage(): string
            {
                return $this->fulfilledStatusMessage;
            }

            protected function getUnfulfilledStatusMessage(): string
            {
                return $this->unfulfilledStatusMessage;
            }
        };
    }

    /**
     * @covers ::getDescription
     * @covers ::getName
     * @covers ::getStatusMessage
     * @covers ::isFulfilled
     *
     * @dataProvider providerBasicFunctionality
     */
    public function testBasicFunctionality(
        $name,
        $description,
        $isFulfilled,
        $fulfilledStatusMessage,
        $unfulfilledStatusMessage,
        $expectedStatusMessage
    ): void {
        $activeDir = $this->activeDir->reveal();
        $stagingDir = $this->stagingDir->reveal();

        // Pass a mock child into the SUT so the behavior of ::isFulfilled can
        // be controlled indirectly, without overriding the method on the SUT
        // itself and preventing it from actually being exercised.
        /** @var \PhpTuf\ComposerStager\Domain\Service\Precondition\PreconditionInterface|\Prophecy\Prophecy\ObjectProphecy $child */
        $child = $this->prophesize(PreconditionInterface::class);
        $child->isFulfilled($activeDir, $stagingDir)
            ->willReturn($isFulfilled);
        $child = $child->reveal();

        $sut = $this->createSut($child);

        $sut->name = $name;
        $sut->description = $description;
        $sut->isFulfilled = $isFulfilled;
        $sut->fulfilledStatusMessage = $fulfilledStatusMessage;
        $sut->unfulfilledStatusMessage = $unfulfilledStatusMessage;

        self::assertEquals($sut->getName(), $name);
        self::assertEquals($sut->getDescription(), $description);
        self::assertEquals($sut->isFulfilled($activeDir, $stagingDir), $isFulfilled);
        self::assertEquals($sut->getStatusMessage($activeDir, $stagingDir), $expectedStatusMessage);
    }

    public function providerBasicFunctionality(): array
    {
        return [
            [
                'name' => 'Name 1',
                'description' => 'Description 1',
                'isFulfilled' => true,
                'fulfilledStatusMessage' => 'Fulfilled status message 1',
                'unfulfilledStatusMessage' => 'Unfulfilled status message 1',
                'expectedStatusMessage' => 'Fulfilled status message 1',
            ],
            [
                'name' => 'Name 2',
                'description' => 'Description 2',
                'isFulfilled' => false,
                'fulfilledStatusMessage' => 'Fulfilled status message 2',
                'unfulfilledStatusMessage' => 'Unfulfilled status message 2',
                'expectedStatusMessage' => 'Unfulfilled status message 2',
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::assertIsFulfilled
     * @covers ::getLeaves
     * @covers ::isFulfilled
     *
     * @uses \PhpTuf\ComposerStager\Domain\Exception\PreconditionException
     */
    public function testIsFulfilledBubbling(): void
    {
        $this->expectException(PreconditionException::class);

        $activeDir = $this->activeDir->reveal();
        $stagingDir = $this->stagingDir->reveal();

        $createLeaf = function (bool $return) use ($activeDir, $stagingDir): PreconditionInterface {
            /** @var \PhpTuf\ComposerStager\Domain\Service\Precondition\PreconditionInterface|\Prophecy\Prophecy\ObjectProphecy $mock */
            $mock = $this->prophesize(PreconditionInterface::class);
            $mock->isFulfilled($activeDir, $stagingDir)
                // Call once for ::isFulfilled() and a second time for ::assertIsFulfilled().
                ->shouldBeCalledTimes(2)
                ->willReturn($return);
            return $mock->reveal();
        };

        $leaves = [
            $createLeaf(true),
            $createLeaf(true),
            $createLeaf(true),
            $createLeaf(false),
        ];

        $sut = $this->createSut(
            $leaves[0],
            $this->createSut(
                $this->createSut(
                    $leaves[1]
                )
            ),
            $this->createSut(
                $this->createSut(
                    $this->createSut(
                        $this->createSut(
                            $leaves[2]
                        )
                    )
                )
            ),
            $this->createSut(
                $this->createSut(
                    $this->createSut(
                        $this->createSut(
                            $this->createSut(
                                $this->createSut(
                                    $this->createSut(
                                        $this->createSut(
                                            $this->createSut(
                                                $this->createSut(
                                                    $leaves[3]
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        self::assertFalse($sut->isFulfilled($activeDir, $stagingDir));
        self::assertSame($leaves, $sut->getLeaves());

        // This is called last so as not to throw the exception until all other
        // assertions have been made.
        $sut->assertIsFulfilled($activeDir, $stagingDir);
    }
}