<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
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

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function matchItem(ItemInterface $item): ?bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $itemUri = $item->getUri();
        if (null === $itemUri) {
            return null;
        }

        if ($itemUri === $request->getRequestUri()) {
            // URL's completely match
            return true;
        }
        if ($itemUri !== $request->getBaseUrl() . '/'
            && 0 === mb_strpos($request->getRequestUri(), $itemUri)) {
            // URL isn't just "/" and the first part of the URL match
            return true;
        }

        return null;
    }
}
