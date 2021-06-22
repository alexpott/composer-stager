<?php

namespace PhpTuf\ComposerStager\Infrastructure\Process\Runner;

use PhpTuf\ComposerStager\Infrastructure\Process\Runner\AbstractRunner;

/**
 * @internal
 */
class ComposerRunner extends AbstractRunner
{
    protected function executableName(): string
    {
        return 'composer'; // @codeCoverageIgnore
    }
}