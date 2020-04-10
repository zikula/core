<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Helper;

use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use stdClass;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class ComposerValidationHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * JSON DECODING ERROR CODE DEFINITIONS
     * @var array
     */
    private $jsonErrorCodes = [
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    ];

    /**
     * Name of bundle owning the json file
     * @var string
     */
    private $bundleName;

    /**
     * The json file's path
     * @var string
     */
    private $filePath;

    /**
     * The json file's raw content
     * @var string
     */
    private $rawContent;

    /**
     * The decoded content
     * @var stdClass
     */
    private $content;

    /**
     * Decoding and validation errors discovered during checking the file
     * @var array
     */
    private $errors = [];

    public function __construct(ZikulaHttpKernelInterface $kernel, TranslatorInterface $translator)
    {
        $this->kernel = $kernel;
        $this->translator = $translator;
    }

    /**
     * Checks a composer file.
     */
    public function check(SplFileInfo $file): void
    {
        // reset errors to clear results from previous calls
        $this->errors = [];

        // we determine the bundle name from the file system on purpose
        // because if a composer file is invalid the bundle is not detected
        // so we can not use AbstractExtension methods here
        $pathParts = explode('/', $file->getRelativePath());
        $this->bundleName = $pathParts[count($pathParts) - 1];

        $this->filePath = $file->getRelativePath();
        $this->rawContent = $file->getContents();

        if ($this->decodeContent()) {
            $this->validateAgainstSchema();
        }
    }

    /**
     * Decodes the content of the current file.
     */
    private function decodeContent(): bool
    {
        $this->content = json_decode($this->rawContent); // returns null on failure
        if (empty($this->content)) {
            $error = $this->jsonErrorCodes[json_last_error()];
            $this->errors[] = $this->translator->trans(
                'Unable to decode composer file of %component% (%filePath%): %error%. Ensure the composer.json file has a valid syntax.',
                [
                    '%component%' => $this->bundleName,
                    '%filePath%' => $this->filePath,
                    '%error%' => $error
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * Validates the composer file against the schema file.
     */
    private function validateAgainstSchema(): void
    {
        $schemaPath = $this->kernel->getModule('ZikulaExtensionsModule')->getPath() . '/Schema/schema.composer.json';

        // Get the schema and data as objects
        $retriever = new UriRetriever();
        $schemaFile = $retriever->retrieve('file://' . realpath($schemaPath));

        // Validate
        $validator = new Validator();
        $validator->check($this->content, $schemaFile);

        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $errorDetails) {
                $this->errors[] = $this->translator->trans(
                    'Error found in composer file of %component% (%filePath%) in property "%property%": %error%.',
                    [
                        '%component%' => $this->bundleName,
                        '%filePath%' => $this->filePath,
                        '%property%' => $errorDetails['property'],
                        '%error%' => $errorDetails['message']
                    ]
                );
            }
        }
    }

    public function isValid(): bool
    {
        //return 1 < count($this->getErrors());
        return empty($this->getErrors());
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
