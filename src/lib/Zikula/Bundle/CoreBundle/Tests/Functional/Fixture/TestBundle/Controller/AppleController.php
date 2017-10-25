<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Functional\Fixture\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/apples")
 */
class AppleController
{
    /**
     * @Route("/view/{d}")
     * @Template("TestBundle:Apple:view.html.twig")
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
     * @Template("TestBundle:Apple:translated.html.twig")
     *
     * @return array
     */
    public function translatedAction()
    {
        return [];
    }
}
