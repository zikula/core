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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/theme')]
#[IsGranted('ROLE_ADMIN')]
class BrandingController extends AbstractController
{
    #[Route('/overview', name: 'zikulathemebundle_branding_overview')]
    public function overview(): Response
    {
        return $this->render('@ZikulaTheme/Branding/overview.html.twig', []);
    }
}
