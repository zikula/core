<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\CategoriesModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class CategoryModelTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $registryId;

    /**
     * @param string $registryId
     */
    public function __construct($registryId)
    {
        $this->registryId = $registryId;
    }

    /**
     * @param null $array
     * @return \Zikula\CategoriesModule\Entity\CategoryEntity
     */
    public function transform($array)
    {
        if (!empty($array) && isset($array[$this->registryId])) {
            /** @var ArrayCollection $arrayCollection */
            $arrayCollection = $array[$this->registryId];
            return $arrayCollection->getValues();
        }

        return array();
    }

    /**
     * @param mixed $arrayValue
     * @return string
     */
    public function reverseTransform($arrayValue)
    {
        return empty($arrayValue) ? array() : array($this->registryId => $arrayValue);
    }
}