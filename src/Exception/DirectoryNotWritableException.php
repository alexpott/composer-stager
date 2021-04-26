<?php

namespace PhpTuf\ComposerStager\Exception;

use Throwable;

class DirectoryNotWritableException extends PathException
{
    public function __construct(
        string $path,
        string $message = 'Directory not writable: "%s"',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($path, $message, $code, $previous);
    }
}