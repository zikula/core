<?php

namespace Gedmo\Translatable\Mapping\Driver;

use Gedmo\Mapping\Driver\File,
    Gedmo\Mapping\Driver,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Exception\InvalidMappingException;

/**
 * This is a yaml mapping driver for Translatable
 * behavioral extension. Used for extraction of extended
 * metadata from yaml specificaly for Translatable
 * extension.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Translatable.Mapping.Driver
 * @subpackage Yaml
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Yaml extends File implements Driver
{
    /**
     * File extension
     * @var string
     */
    protected $_extension = '.dcm.yml';

    /**
     * {@inheritDoc}
     */
    public function validateFullMetadata(ClassMetadata $meta, array $config)
    {
        if ($config && is_array($meta->identifier) && count($meta->identifier) > 1) {
            throw new InvalidMappingException("Translatable does not support composite identifiers in class - {$meta->name}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata(ClassMetadata $meta, array &$config)
    {
        $mapping = $this->_getMapping($meta->name);

        if (isset($mapping['gedmo'])) {
            $classMapping = $mapping['gedmo'];
            if (isset($classMapping['translation']['entity'])) {
                $translationEntity = $classMapping['translation']['entity'];
                if (!class_exists($translationEntity)) {
                    throw new InvalidMappingException("Translation entity class: {$translationEntity} does not exist.");
                }
                $config['translationClass'] = $translationEntity;
            }
            if (isset($classMapping['translation']['locale'])) {
                $config['locale'] = $classMapping['translation']['locale'];
            } elseif (isset($classMapping['translation']['language'])) {
                $config['locale'] = $classMapping['translation']['language'];
            }
        }
        if (isset($mapping['fields'])) {
            foreach ($mapping['fields'] as $field => $fieldMapping) {
                if (isset($fieldMapping['gedmo'])) {
                    if (in_array('translatable', $fieldMapping['gedmo'])) {
                        // fields cannot be overrided and throws mapping exception
                        $config['fields'][] = $field;
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function _loadMappingFile($file)
    {
        return \Symfony\Component\Yaml\Yaml::load($file);
    }
}
