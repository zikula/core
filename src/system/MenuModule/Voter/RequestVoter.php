<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RequestVoter
 * @see https://gist.github.com/nateevans/9958390
 */
class RequestVoter implements VoterInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * RequestVoter constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param ItemInterface $item
     * @return bool|null
     */
    public function matchItem(ItemInterface $item)
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($item->getUri() === $request->getRequestUri()) {
            // URL's completely match
            return true;
        } elseif ($item->getUri() !== $request->getBaseUrl() . '/'
            && substr($request->getRequestUri(), 0, strlen($item->getUri())) === $item->getUri()) {
            // URL isn't just "/" and the first part of the URL match
            return true;
        }

        return null;
    }
}
