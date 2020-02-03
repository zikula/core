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

namespace Zikula\BootstrapTheme\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

/**
 * Theme specific ajax controller implementation class.
 *
 * @Route("/ajax")
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/changeUserStyle", methods = {"POST"}, options={"expose"=true})
     */
    public function changeUserStyleAction(
        Request $request,
        ZikulaHttpKernelInterface $kernel
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return $this->json($this->trans('Only ajax access is allowed!'), Response::HTTP_BAD_REQUEST);
        }

        $themeBundle = $kernel->getBundle('ZikulaBootstrapTheme');
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
