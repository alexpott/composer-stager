<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Infrastructure\Precondition\Service;

use PhpTuf\ComposerStager\API\Precondition\Service\NoAbsoluteSymlinksExistInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\NoHardLinksExistInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\NoLinksExistOnWindowsInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\NoSymlinksPointOutsideTheCodebaseInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\NoSymlinksPointToADirectoryInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\NoUnsupportedLinksExistInterface;
use PhpTuf\ComposerStager\API\Translation\Factory\TranslatableFactoryInterface;
use PhpTuf\ComposerStager\API\Translation\Value\TranslatableInterface;

/**
 * @package Precondition
 *
 * @internal Don't instantiate this class directly. Get it from the service container via its interface.
 */
final class NoUnsupportedLinksExist extends AbstractPreconditionsTree implements NoUnsupportedLinksExistInterface
{
    public function __construct(
        NoAbsoluteSymlinksExistInterface $noAbsoluteSymlinksExist,
        NoHardLinksExistInterface $noHardLinksExist,
        NoLinksExistOnWindowsInterface $noLinksExistOnWindows,
        NoSymlinksPointOutsideTheCodebaseInterface $noSymlinksPointOutsideTheCodebase,
        NoSymlinksPointToADirectoryInterface $noSymlinksPointToADirectory,
        TranslatableFactoryInterface $translatableFactory,
    ) {
        parent::__construct(
            $translatableFactory,
            $noAbsoluteSymlinksExist,
            $noHardLinksExist,
            $noLinksExistOnWindows,
            $noSymlinksPointOutsideTheCodebase,
            $noSymlinksPointToADirectory,
        );
    }

    public function getName(): TranslatableInterface
    {
        return $this->t('Unsupported links preconditions');
    }

    public function getDescription(): TranslatableInterface
    {
        return $this->t('Preconditions concerning unsupported links.');
    }

    protected function getFulfilledStatusMessage(): TranslatableInterface
    {
        return $this->t('There are no unsupported links in the codebase.');
    }
}
