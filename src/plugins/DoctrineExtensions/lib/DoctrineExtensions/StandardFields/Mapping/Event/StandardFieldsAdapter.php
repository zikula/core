<?php

namespace DoctrineExtensions\StandardFields\Mapping\Event;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Mapping\Event\AdapterInterface;

/**
 * Doctrine event adapter interface
 * for StandardFields behavior
 */
interface StandardFieldsAdapter extends AdapterInterface
{
    /**
     * Get the user id value
     *
     * @param ClassMetadata $meta
     * @param string $field
     * @return mixed
     */
    function getUserIdValue(ClassMetadata $meta, $field);
}