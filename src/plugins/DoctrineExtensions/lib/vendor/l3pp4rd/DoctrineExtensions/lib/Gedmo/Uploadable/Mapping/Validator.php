<?php

namespace Gedmo\Uploadable\Mapping;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Gedmo\Exception\InvalidMappingException;
use Gedmo\Exception\UploadableCantWriteException;
use Gedmo\Exception\UploadableInvalidPathException;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class is used to validate mapping information
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Uploadable.Mapping
 * @subpackage Validator
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class Validator 
{
    const UPLOADABLE_FILE_MIME_TYPE = 'UploadableFileMimeType';
    const UPLOADABLE_FILE_PATH = 'UploadableFilePath';
    const UPLOADABLE_FILE_SIZE = 'UploadableFileSize';
    const FILENAME_GENERATOR_SHA1 = 'SHA1';
    const FILENAME_GENERATOR_ALPHANUMERIC = 'ALPHANUMERIC';
    const FILENAME_GENERATOR_NONE = 'NONE';

    /**
     * Determines if we should throw an exception in the case the "allowedTypes" and
     * "disallowedTypes" options are BOTH set. Useful for testing purposes
     *
     * @var bool
     */
    public static $enableMimeTypesConfigException = true;

    /**
     * List of types which are valid for UploadableFileMimeType field
     *
     * @var array
     */
    public static $validFileMimeTypeTypes = array(
        'string'
    );

    /**
     * List of types which are valid for UploadableFilePath field
     *
     * @var array
     */
    public static $validFilePathTypes = array(
        'string'
    );

    /**
     * List of types which are valid for UploadableFileSize field
     *
     * @var array
     */
    public static $validFileSizeTypes = array(
        'decimal'
    );


    public static function validateFileMimeTypeField(ClassMetadataInfo $meta, $field)
    {
        self::validateField($meta, $field, self::UPLOADABLE_FILE_MIME_TYPE, self::$validFileMimeTypeTypes);
    }

    public static function validateFilePathField(ClassMetadataInfo $meta, $field)
    {
        self::validateField($meta, $field, self::UPLOADABLE_FILE_PATH, self::$validFilePathTypes);
    }

    public static function validateFileSizeField(ClassMetadataInfo $meta, $field)
    {
        self::validateField($meta, $field, self::UPLOADABLE_FILE_SIZE, self::$validFileSizeTypes);
    }

    public static function validateField($meta, $field, $uploadableField, $validFieldTypes)
    {
        $fieldMapping = $meta->getFieldMapping($field);

        if (!in_array($fieldMapping['type'], $validFieldTypes)) {
            $msg = 'Field "%s" to work as an "%s" field must be of one of the following types: "%s".';

            throw new InvalidMappingException(sprintf($msg,
                $field,
                $uploadableField,
                implode(', ', $validFieldTypes)
            ));
        }
    }

    public static function validatePath($path)
    {
        if (!is_string($path) || $path === '') {
            throw new UploadableInvalidPathException('Path must be a string containing the path to a valid directory.');
        }

        if (!is_dir($path) || !is_writable($path)) {
            throw new UploadableCantWriteException(sprintf('Directory "%s" does not exist or is not writable',
                $path
            ));
        }
    }

    public static function validateConfiguration(ClassMetadata $meta, array &$config)
    {
        if (!$config['filePathField']) {
            throw new InvalidMappingException(sprintf('Class "%s" must have an UploadableFilePath field.',
                $meta->name
            ));
        }

        $refl = $meta->getReflectionClass();

        if ($config['pathMethod'] !== '' && !$refl->hasMethod($config['pathMethod'])) {
            throw new InvalidMappingException(sprintf('Class "%s" doesn\'t have method "%s"!',
                $meta->name,
                $config['pathMethod']
            ));
        }

        if ($config['callback'] !== '' && !$refl->hasMethod($config['callback'])) {
            throw new InvalidMappingException(sprintf('Class "%s" doesn\'t have method "%s"!',
                $meta->name,
                $config['callback']
            ));
        }

        $config['maxSize'] = (double) $config['maxSize'];

        if ($config['maxSize'] < 0) {
            throw new InvalidMappingException(sprintf('Option "maxSize" must be a number >= 0 for class "%s".',
                $meta->name
            ));
        }

        if (self::$enableMimeTypesConfigException && ($config['allowedTypes'] !== '' && $config['disallowedTypes'] !== '')) {
            $msg = 'You\'ve set "allowedTypes" and "disallowedTypes" options. You must set only one in class "%s".';

            throw new InvalidMappingException(sprintf($msg,
                $meta->name
            ));
        }

        $config['allowedTypes'] = $config['allowedTypes'] ? (strpos($config['allowedTypes'], ',') !== false ?
            explode(',', $config['allowedTypes']) : array($config['allowedTypes'])) : false;
        $config['disallowedTypes'] = $config['disallowedTypes'] ? (strpos($config['disallowedTypes'], ',') !== false ?
            explode(',', $config['disallowedTypes']) : array($config['disallowedTypes'])) : false;

        if ($config['fileMimeTypeField']) {
            self::validateFileMimeTypeField($meta, $config['fileMimeTypeField']);
        }

        if ($config['fileSizeField']) {
            self::validateFileSizeField($meta, $config['fileSizeField']);
        }

        switch ((string) $config['filenameGenerator']) {
            case self::FILENAME_GENERATOR_ALPHANUMERIC:
            case self::FILENAME_GENERATOR_SHA1:
            case self::FILENAME_GENERATOR_NONE:
                break;
            default:
                $ok = false;

                if (class_exists($config['filenameGenerator'])) {
                    $refl = new \ReflectionClass($config['filenameGenerator']);

                    if ($refl->implementsInterface('Gedmo\Uploadable\FilenameGenerator\FilenameGeneratorInterface')) {
                        $ok = true;
                    }
                }

                if (!$ok) {
                    $msg = 'Class "%s" needs a valid value for filenameGenerator. It can be: SHA1, ALPHANUMERIC, NONE or ';
                    $msg .= 'a class implementing FileGeneratorInterface.';

                    throw new InvalidMappingException(sprintf($msg,
                        $meta->name
                    ));
                }
        }

        self::validateFilePathField($meta, $config['filePathField']);
    }
}
