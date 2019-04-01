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

namespace Zikula\Bundle\CoreBundle\Tests\Functional\Fixture\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/apples")
 */
class AppleController
{
    /**
     * @Route("/view/{d}")
     * @Template
     */
    public function viewAction(string $d = null): array
    {
        $templateParameters = ['nbApples' => 5];
        if (isset($d)) {
            $templateParameters['domain'] = $d;
        }

        return $templateParameters;
    }

    /**
     * @Route("/t")
     * @Template
     */
    public function translatedAction(): array
    {
        return [];
    }
}
