<?php

namespace Gedmo\Uploadable\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Exception\InvalidMappingException,
    Gedmo\Uploadable\Mapping\Validator;

/**
 * This is an annotation mapping driver for Uploadable
 * behavioral extension. Used for extraction of extended
 * metadata from Annotations specificaly for Uploadable
 * extension.
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Uploadable.Mapping.Driver
 * @subpackage Annotation
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Annotation extends AbstractAnnotationDriver
{
    /**
     * Annotation to define that this object is loggable
     */
    const UPLOADABLE = 'Gedmo\\Mapping\\Annotation\\Uploadable';
    const UPLOADABLE_FILE_MIME_TYPE = 'Gedmo\\Mapping\\Annotation\\UploadableFileMimeType';
    const UPLOADABLE_FILE_PATH = 'Gedmo\\Mapping\\Annotation\\UploadableFilePath';
    const UPLOADABLE_FILE_SIZE = 'Gedmo\\Mapping\\Annotation\\UploadableFileSize';

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);

        // class annotations
        if ($annot = $this->reader->getClassAnnotation($class, self::UPLOADABLE)) {
            $config['uploadable'] = true;
            $config['allowOverwrite'] = $annot->allowOverwrite;
            $config['appendNumber'] = $annot->appendNumber;
            $config['path'] = $annot->path;
            $config['pathMethod'] = $annot->pathMethod;
            $config['fileMimeTypeField'] = false;
            $config['filePathField'] = false;
            $config['fileSizeField'] = false;
            $config['callback'] = $annot->callback;
            $config['filenameGenerator'] = $annot->filenameGenerator;
            $config['maxSize'] = (double) $annot->maxSize;
            $config['allowedTypes'] = $annot->allowedTypes;
            $config['disallowedTypes'] = $annot->disallowedTypes;

            foreach ($class->getProperties() as $prop) {
                if ($this->reader->getPropertyAnnotation($prop, self::UPLOADABLE_FILE_MIME_TYPE)) {
                    $config['fileMimeTypeField'] = $prop->getName();
                }

                if ($this->reader->getPropertyAnnotation($prop, self::UPLOADABLE_FILE_PATH)) {
                    $config['filePathField'] = $prop->getName();
                }

                if ($this->reader->getPropertyAnnotation($prop, self::UPLOADABLE_FILE_SIZE)) {
                    $config['fileSizeField'] = $prop->getName();
                }
            }

            Validator::validateConfiguration($meta, $config);
        }

        /*
        // Code in case we need to identify entities which are not Uploadables, but have associations
        // with other Uploadable entities

        } else {
            // We need to check if this class has a relation with Uploadable entities
            $associations = $meta->getAssociationMappings();

            foreach ($associations as $field => $association) {
                $refl = new \ReflectionClass($association['targetEntity']);

                if ($annot = $this->reader->getClassAnnotation($refl, self::UPLOADABLE)) {
                    $config['hasUploadables'] = true;

                    if (!isset($config['uploadables'])) {
                        $config['uploadables'] = array();
                    }

                    $config['uploadables'][] = array(
                        'class'         => $association['targetEntity'],
                        'property'      => $association['fieldName']
                    );
                }
            }
        }*/

        $this->validateFullMetadata($meta, $config);
    }
}
