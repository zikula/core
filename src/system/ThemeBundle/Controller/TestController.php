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

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zikula\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ThemeBundle\Form\Type\MailTestType;
use Zikula\ThemeBundle\Helper\FallbackDashboardDetector;

class TestController extends AbstractController
{
    #[Route('/test/page', name: 'zikulathemebundle_test_page')]
    #[Route('/admin/test/page', name: 'zikulathemebundle_admin_test_page')]
    public function lala(Request $request, FallbackDashboardDetector $dashboardDetector): Response
    {
        return $this->render('@ZikulaTheme/Test/page.html.twig',
            [
                'isAdminArea' => $request->attributes->get('isAdminArea', false),
            ]
        );
    }
}
