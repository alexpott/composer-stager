<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Domain\Factory\Translation;

use PhpTuf\ComposerStager\Domain\Value\Translation\TranslatableInterface;
use PhpTuf\ComposerStager\Domain\Value\Translation\TranslationParametersInterface;

/**
 * Creates translatable objects.
 *
 * @package Translation
 *
 * @api
 */
interface TranslatableFactoryInterface
{
    /**
     * Creates a translatable message.
     *
     * @param string $message
     *   A message containing optional placeholders corresponding to parameters (next). Example:
     *   ```php
     *   $message = 'Hello, %first_name %last_name.';
     *   ```
     * @param \PhpTuf\ComposerStager\Domain\Value\Translation\TranslationParametersInterface|null $parameters
     *   Parameters for the message.
     * @param string|null $domain
     *   An arbitrary domain for grouping translations, e.g., "app", "admin",
     *   "store", or null to use the default.
     */
    public function createTranslatableMessage(
        string $message,
        ?TranslationParametersInterface $parameters = null,
        ?string $domain = null,
    ): TranslatableInterface;

    /**
     * Creates a translation parameters object.
     *
     * @param array<string, string> $parameters
     *   An associative array keyed by placeholders with their corresponding substitution
     *   values. Placeholders must be in the form /^%\w+$/, i.e., a leading percent sign (%)
     *   followed by one or more alphanumeric characters and underscores, e.g., "%example".
     *   Values must be strings. Example:
     *   ```php
     *   $parameters = [
     *     '%first_name' => 'John',
     *     '%last_name' => 'Doe',
     *   ];
     *   ```
     */
    public function createTranslationParameters(array $parameters = []): TranslationParametersInterface;
}