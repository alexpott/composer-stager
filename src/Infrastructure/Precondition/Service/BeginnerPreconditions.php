<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Infrastructure\Precondition\Service;

use PhpTuf\ComposerStager\API\Precondition\Service\BeginnerPreconditionsInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\CommonPreconditionsInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\NoUnsupportedLinksExistInterface;
use PhpTuf\ComposerStager\API\Precondition\Service\StagingDirDoesNotExistInterface;
use PhpTuf\ComposerStager\API\Translation\Factory\TranslatableFactoryInterface;
use PhpTuf\ComposerStager\API\Translation\Value\TranslatableInterface;

/**
 * @package Precondition
 *
 * @internal Don't instantiate this class directly. Get it from the service container via its interface.
 */
final class BeginnerPreconditions extends AbstractPreconditionsTree implements BeginnerPreconditionsInterface
{
    public function __construct(
        CommonPreconditionsInterface $commonPreconditions,
        NoUnsupportedLinksExistInterface $noUnsupportedLinksExist,
        StagingDirDoesNotExistInterface $stagingDirDoesNotExist,
        TranslatableFactoryInterface $translatableFactory,
    ) {
        parent::__construct(
            $translatableFactory,
            $commonPreconditions,
            $noUnsupportedLinksExist,
            $stagingDirDoesNotExist,
        );
    }

    public function getName(): TranslatableInterface
    {
        return $this->t('Beginner preconditions');
    }

    public function getDescription(): TranslatableInterface
    {
        return $this->t('The preconditions for beginning the staging process.');
    }

    protected function getFulfilledStatusMessage(): TranslatableInterface
    {
        return $this->t('The preconditions for beginning the staging process are fulfilled.');
    }
}
