<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Api;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\BlocksModule\Api\ApiInterface\BlockFilterApiInterface;
use Zikula\BlocksModule\Entity\BlockEntity;

/**
 * Class BlockFilterApi
 */
class BlockFilterApi implements BlockFilterApiInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function isDisplayable(BlockEntity $blockEntity): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return true;
        }

        // filter for language/locale
        $language = $blockEntity->getLanguage();
        if (!empty($language) && $language !== $request->getLocale()) {
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
                    $name = $params[$filter['queryParameter']] ?? 'kjashdhk11111'; // random characters to prevent match
                    break;
                default:
                    $name = $request->attributes->get($filter['attribute']);
            }
            if (empty($name)) {
                continue;
            }
            $displayable = $displayable && $this->compare($name, $filter['comparator'], $filter['value']);
        }

        return $displayable;
    }

    /**
     * Compare variables according to a dynamic comparator.
     */
    private function compare(string $var1, string $comparator, string $var2): bool
    {
        switch ($comparator) {
            case '==':
                return $var1 === $var2;
            case '!=':
                return $var1 !== $var2;
            case '>=':
                return $var1 >= $var2;
            case '<=':
                return $var1 <= $var2;
            case '>':
                return $var1 > $var2;
            case '<':
                return $var1 < $var2;
            case 'in_array':
                $var2Array = array_map('trim', explode(',', $var2));

                return in_array($var1, $var2Array, true);
            case '!in_array':
                $var2Array = array_map('trim', explode(',', $var2));

                return !in_array($var1, $var2Array, true);
            default:
                return true;
        }
    }

    public function getFilterAttributeChoices(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
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
