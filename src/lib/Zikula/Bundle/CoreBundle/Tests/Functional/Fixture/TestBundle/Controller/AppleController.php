<?php

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
     *
     * @param null $d
     * @return array
     */
    public function viewAction($d = null)
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
     *
     * @return array
     */
    public function translatedAction()
    {
        return [];
    }
}
