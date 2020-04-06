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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Component\Wizard\WizardCompleteInterface;

class CompleteStage implements StageInterface, WizardCompleteInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getName(): string
    {
        return 'complete';
    }

    public function getTemplateName(): string
    {
        return '';
    }

    public function isNecessary(): bool
    {
        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    public function getResponse(Request $request): Response
    {
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->getFlashBag()->add('success', 'Congratulations! Upgrade Complete.');
        }

        return new RedirectResponse($this->router->generate('zikulaadminmodule_admin_adminpanel', [], RouterInterface::ABSOLUTE_URL));
    }
}
