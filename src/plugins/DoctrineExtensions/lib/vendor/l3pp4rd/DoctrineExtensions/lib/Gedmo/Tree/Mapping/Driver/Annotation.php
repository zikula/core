<?php

namespace Gedmo\Tree\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver,
    Gedmo\Exception\InvalidMappingException,
    Gedmo\Tree\Mapping\Validator;

/**
 * This is an annotation mapping driver for Tree
 * behavioral extension. Used for extraction of extended
 * metadata from Annotations specificaly for Tree
 * extension.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Tree.Mapping.Driver
 * @subpackage Annotation
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Annotation extends AbstractAnnotationDriver
{
    /**
     * Annotation to define the tree type
     */
    const TREE = 'Gedmo\\Mapping\\Annotation\\Tree';

    /**
     * Annotation to mark field as one which will store left value
     */
    const LEFT = 'Gedmo\\Mapping\\Annotation\\TreeLeft';

    /**
     * Annotation to mark field as one which will store right value
     */
    const RIGHT = 'Gedmo\\Mapping\\Annotation\\TreeRight';

    /**
     * Annotation to mark relative parent field
     */
    const PARENT = 'Gedmo\\Mapping\\Annotation\\TreeParent';

    /**
     * Annotation to mark node level
     */
    const LEVEL = 'Gedmo\\Mapping\\Annotation\\TreeLevel';

    /**
     * Annotation to mark field as tree root
     */
    const ROOT = 'Gedmo\\Mapping\\Annotation\\TreeRoot';

    /**
     * Annotation to specify closure tree class
     */
    const CLOSURE = 'Gedmo\\Mapping\\Annotation\\TreeClosure';

    /**
     * Annotation to specify path class
     */
    const PATH = 'Gedmo\\Mapping\\Annotation\\TreePath';

    /**
     * Annotation to specify path source class
     */
    const PATH_SOURCE = 'Gedmo\\Mapping\\Annotation\\TreePathSource';

    /**
     * Annotation to mark the field to be used to hold the lock time
     */
    const LOCK_TIME = 'Gedmo\\Mapping\\Annotation\\TreeLockTime';

    /**
     * List of tree strategies available
     *
     * @var array
     */
    protected $strategies = array(
        'nested',
        'closure',
        'materializedPath'
    );

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $validator = new Validator();
        $class = $this->getMetaReflectionClass($meta);
        // class annotations
        if ($annot = $this->reader->getClassAnnotation($class, self::TREE)) {
            if (!in_array($annot->type, $this->strategies)) {
                throw new InvalidMappingException("Tree type: {$annot->type} is not available.");
            }
            $config['strategy'] = $annot->type;
            $config['activate_locking'] = $annot->activateLocking;
            $config['locking_timeout'] = (int) $annot->lockingTimeout;

            if ($config['locking_timeout'] < 1) {
                throw new InvalidMappingException("Tree Locking Timeout must be at least of 1 second.");
            }
        }
        if ($annot = $this->reader->getClassAnnotation($class, self::CLOSURE)) {
            if (!class_exists($annot->class)) {
                throw new InvalidMappingException("Tree closure class: {$annot->class} does not exist.");
            }
            $config['closure'] = $annot->class;
        }

        // property annotations
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }
            // left
            if ($this->reader->getPropertyAnnotation($property, self::LEFT)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'left' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$validator->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Tree left field - [{$field}] type is not valid and must be 'integer' in class - {$meta->name}");
                }
                $config['left'] = $field;
            }
            // right
            if ($this->reader->getPropertyAnnotation($property, self::RIGHT)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'right' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$validator->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Tree right field - [{$field}] type is not valid and must be 'integer' in class - {$meta->name}");
                }
                $config['right'] = $field;
            }
            // ancestor/parent
            if ($this->reader->getPropertyAnnotation($property, self::PARENT)) {
                $field = $property->getName();
                if (!$meta->isSingleValuedAssociation($field)) {
                    throw new InvalidMappingException("Unable to find ancestor/parent child relation through ancestor field - [{$field}] in class - {$meta->name}");
                }
                $config['parent'] = $field;
            }
            // root
            if ($this->reader->getPropertyAnnotation($property, self::ROOT)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'root' - [{$field}] as mapped property in entity - {$meta->name}");
                }

                if (!$validator->isValidFieldForRoot($meta, $field)) {
                    throw new InvalidMappingException("Tree root field - [{$field}] type is not valid and must be any of the 'integer' types or 'string' in class - {$meta->name}");
                }
                $config['root'] = $field;
            }
            // level
            if ($this->reader->getPropertyAnnotation($property, self::LEVEL)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'level' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$validator->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Tree level field - [{$field}] type is not valid and must be 'integer' in class - {$meta->name}");
                }
                $config['level'] = $field;
            }
            // path
            if ($pathAnnotation = $this->reader->getPropertyAnnotation($property, self::PATH)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'path' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$validator->isValidFieldForPath($meta, $field)) {
                    throw new InvalidMappingException("Tree Path field - [{$field}] type is not valid. It must be string or text in class - {$meta->name}");
                }
                if (strlen($pathAnnotation->separator) > 1) {
                    throw new InvalidMappingException("Tree Path field - [{$field}] Separator {$pathAnnotation->separator} is invalid. It must be only one character long.");
                }
                $config['path'] = $field;
                $config['path_separator'] = $pathAnnotation->separator;
            }
            // path source
            if ($this->reader->getPropertyAnnotation($property, self::PATH_SOURCE)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'path_source' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$validator->isValidFieldForPathSource($meta, $field)) {
                    throw new InvalidMappingException("Tree PathSource field - [{$field}] type is not valid. It can be any of the integer variants, double, float or string in class - {$meta->name}");
                }
                $config['path_source'] = $field;
            }
            // lock time

            if ($this->reader->getPropertyAnnotation($property, self::LOCK_TIME)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'lock_time' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$validator->isValidFieldForLockTime($meta, $field)) {
                    throw new InvalidMappingException("Tree PathSource field - [{$field}] type is not valid. It must be \"date\" in class - {$meta->name}");
                }
                $config['lock_time'] = $field;
            }
        }

        if (isset($config['activate_locking']) && $config['activate_locking'] && !isset($config['lock_time'])) {
            throw new InvalidMappingException("You need to map a date field as the tree lock time field to activate locking support.");
        }

        if (!$meta->isMappedSuperclass && $config) {
            if (isset($config['strategy'])) {
                if (is_array($meta->identifier) && count($meta->identifier) > 1) {
                    throw new InvalidMappingException("Tree does not support composite identifiers in class - {$meta->name}");
                }
                $method = 'validate' . ucfirst($config['strategy']) . 'TreeMetadata';
                $validator->$method($meta, $config);
            } else {
                throw new InvalidMappingException("Cannot find Tree type for class: {$meta->name}");
            }
        }
    }
}
