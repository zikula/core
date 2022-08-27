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

namespace Zikula\DefaultThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/default-theme/ajax')]
class AjaxController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/changeUserStyle', name: 'zikuladefaultthemebundle_ajax_changeuserstyle', methods: ['POST'], options: ['expose' => true])]
    public function changeUserStyle(Request $request, KernelInterface $kernel): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json($this->translator->trans('Only ajax access is allowed!'), Response::HTTP_BAD_REQUEST);
        }

        $themeBundle = $kernel->getBundle('ZikulaDefaultThemeBundle');
        $themeVarsPath = $themeBundle->getConfigPath() . '/variables.yaml';
        $variableDefinitions = Yaml::parse(file_get_contents($themeVarsPath));
        $availableStyles = $variableDefinitions['theme_style']['options']['choices'];

        $style = $request->request->get('style', '');
        if (!$style || !in_array($style, array_values($availableStyles), true)) {
            return $this->json(['result' => false]);
        }

        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set('currentBootstrapStyle', $style);
        }

        return $this->json(['result' => true]);
    }
}
