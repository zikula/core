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

namespace Zikula\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test/page', name: 'zikulathemebundle_test_page')]
    #[Route('/admin/test/page', name: 'zikulathemebundle_admin_test_page')]
    public function lala(Request $request): Response
    {
        return $this->render('@ZikulaTheme/Test/page.html.twig',
            [
                'isAdminArea' => $request->attributes->get('isAdminArea', false),
            ]
        );
    }
}
