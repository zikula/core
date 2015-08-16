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

class CategoryDisplayTransformer implements DataTransformerInterface
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
     * @param $category
     * @return array
     */
    public function transform($category)
    {
        return $category;
    }

    /**
     * @param mixed $arrayValue
     * @return string
     */
    public function reverseTransform($arrayValue)
    {
        return is_array($arrayValue) ? $arrayValue[$this->registryId] : '';
    }
}