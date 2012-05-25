<?php

namespace Zikula\Core\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 *
 */
class CategoriesCollectionTransformer implements DataTransformerInterface
{
    private $cls;

    public function __construct($cls)
    {
        $this->cls = $cls;
    }

    public function reverseTransform($value)
    {
        $collection = new ArrayCollection();
        $class = $this->cls;

        foreach($value as $regId => $category) {
            $regId = (int) substr($regId, strpos($regId, '_') + 1);
            $collection->set($regId, new $class($regId, $category, null));
        }

        return $collection;
    }

    public function transform($value)
    {
        if(!$value instanceof Collection) {
            return null;
        }

        $data = array();

        foreach($value as $key => $entityCategory) {
            $data['registry_' . $key] = $entityCategory->getCategory();
        }

        return $data;
    }
}
