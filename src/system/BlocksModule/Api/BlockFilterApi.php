<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Api;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\BlocksModule\Entity\BlockEntity;

/**
 * Class BlockFilterApi
 */
class BlockFilterApi
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * BlockApi constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Determine if the block is displayable based on the filter criteria.
     *
     * @param BlockEntity $blockEntity
     * @return boolean
     */
    public function isDisplayable(BlockEntity $blockEntity)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return true;
        }

        // filter for language/locale
        $language = $blockEntity->getLanguage();
        if (!empty($language) && ($language != $request->getLocale())) {
            return false;
        }

        $displayable = true;
        $filters = $blockEntity->getFilters();
        foreach ($filters as $filter) {
            switch ($filter['attribute']) {
                case 'query param':
                    $name = $request->query->get($filter['queryParameter']);
                    break;
                case '_route_params':
                    $params = $request->attributes->get('_route_params');
                    $name = isset($params[$filter['queryParameter']]) ? $params[$filter['queryParameter']] : 'kjashdhk11111'; // random characters to prevent match
                    break;
                default:
                    $name = $request->attributes->get($filter['attribute']);
            }
            $displayable = $displayable && $this->compare($name, $filter['comparator'], $filter['value']);
        }

        return $displayable;
    }

    /**
     * Compare variables according to a dynamic comparator.
     *
     * @param $var1
     * @param $comparator
     * @param $var2
     * @return bool
     */
    private function compare($var1, $comparator, $var2)
    {
        switch ($comparator) {
            case "==":
                return $var1 == $var2;
            case "!=":
                return $var1 != $var2;
            case ">=":
                return $var1 >= $var2;
            case "<=":
                return $var1 <= $var2;
            case ">":
                return $var1 > $var2;
            case "<":
                return $var1 < $var2;
            case "in_array":
                $var2 = array_map('trim', explode(',', $var2));

                return in_array($var1, $var2);
            case "!in_array":
                $var2 = array_map('trim', explode(',', $var2));

                return !in_array($var1, $var2);
            default:
                return true;
        }
    }

    /**
     * Get all the attributes of the request + 'query param'.
     *
     * @return array
     */
    public function getFilterAttributeChoices()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null == $request) {
            return [];
        }
        $attributes = [];
        foreach ($request->attributes->keys() as $attribute) {
            $attributes[$attribute] = $attribute;
        }
        $attributes['query param'] = 'query param';

        return $attributes;
    }
}
