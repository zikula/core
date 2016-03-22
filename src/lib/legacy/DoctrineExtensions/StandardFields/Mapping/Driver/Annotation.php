<?php

namespace DoctrineExtensions\StandardFields\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Exception\InvalidMappingException;
use Gedmo\Mapping\Driver\AnnotationDriverInterface;

/**
 * This is an annotation mapping driver for StandardFields
 * behavioral extension. Used for extraction of extended
 * metadata from Annotations specificaly for StandardFields
 * extension.
 */
class Annotation implements AnnotationDriverInterface
{
    /**
     * Annotation field is timestampable
     */
    const TIMESTAMPABLE = 'DoctrineExtensions\\StandardFields\\Mapping\\Annotation\\StandardFields';

    /**
     * List of types which are valid for timestamp
     *
     * @var array
     */
    private $validTypes = array(
        'integer'
    );

    /**
     * Annotation reader instance
     *
     * @var object
     */
    private $reader;

    /**
     * original driver if it is available
     */
    protected $_originalDriver = null;

    /**
     * {@inheritdoc}
     */
    public function setAnnotationReader($reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function validateFullMetadata(ClassMetadata $meta, array $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $meta->getReflectionClass();
        // property annotations
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }
            if ($timestampable = $this->reader->getPropertyAnnotation($property, self::TIMESTAMPABLE)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find StandardFields [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$this->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Field - [{$field}] type is not valid and must be 'integer' in class - {$meta->name}");
                }
                if ($timestampable->type != 'userid') {
                    throw new InvalidMappingException("Field - [{$field}] StandardFields annotation attribute 'type' is not 'userid' in class - {$meta->name}");
                }
                if (!in_array($timestampable->on, array('update', 'create', 'change'))) {
                    throw new InvalidMappingException("Field - [{$field}] trigger 'on' is not one of [update, create, change] in class - {$meta->name}");
                }
                if ($timestampable->on == 'change') {
                    if (!isset($timestampable->field) || !isset($timestampable->value)) {
                        throw new InvalidMappingException("Missing parameters on property - {$field}, field and value must be set on [change] trigger in class - {$meta->name}");
                    }
                    $field = array(
                        'field'        => $field,
                        'trackedField' => $timestampable->field,
                        'value'        => $timestampable->value
                    );
                }
                // properties are unique and mapper checks that, no risk here
                $config[$timestampable->on][] = $field;
            }
        }
    }

    /**
     * Checks if $field type is valid
     *
     * @param ClassMetadata $meta
     * @param string        $field
     *
     * @return boolean
     */
    protected function isValidField(ClassMetadata $meta, $field)
    {
        $mapping = $meta->getFieldMapping($field);

        return $mapping && in_array($mapping['type'], $this->validTypes);
    }

    /**
     * Passes in the mapping read by original driver
     *
     * @param $driver
     *
     * @return void
     */
    public function setOriginalDriver($driver)
    {
        $this->_originalDriver = $driver;
    }
}
