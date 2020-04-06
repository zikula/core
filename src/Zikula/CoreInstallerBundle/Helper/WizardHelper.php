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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\StageContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Component\Wizard\Wizard;
use Zikula\Component\Wizard\WizardCompleteInterface;

class WizardHelper
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var StageContainerInterface
     */
    private $stageContainer;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var string
     */
    private $locale;

    public function __construct(
        RouterInterface $router,
        StageContainerInterface $stageContainer,
        Environment $twig,
        FormFactoryInterface $formFactory,
        string $locale
    ) {
        $this->router = $router;
        $this->stageContainer = $stageContainer;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->locale = $locale;
    }

    /**
     * Executes the wizard for installation or upgrade process.
     */
    public function processWizard(Request $request, string $stage, $mode = 'install', YamlDumper $yamlDumper = null): Response
    {
        if (!in_array($mode, ['install', 'upgrade'])) {
            $mode = 'install';
        }
        $session = $request->hasSession() ? $request->getSession() : null;

        // begin the wizard
        $wizard = new Wizard($this->stageContainer, dirname(__DIR__) . '/Resources/config/' . $mode . '_stages.yaml');
        $currentStage = $wizard->getCurrentStage($stage);
        if ($currentStage instanceof WizardCompleteInterface) {
            if ('upgrade' === $mode && null !== $yamlDumper) {
                $yamlDumper->setParameter('upgrading', false);
            }

            return $currentStage->getResponse($request);
        }

        $templateParams = $this->getTemplateGlobals($currentStage);
        $templateParams['headertemplate'] = '@ZikulaCoreInstaller/' . $mode . 'Header.html.twig';
        if ($wizard->isHalted()) {
            if (null !== $session) {
                $session->getFlashBag()->add('danger', $wizard->getWarning());
            }

            return $this->renderResponse('@ZikulaCoreInstaller/error.html.twig', $templateParams);
        }

        // handle the form
        if ($currentStage instanceof FormHandlerInterface) {
            $form = $this->formFactory->create($currentStage->getFormType(), null, $currentStage->getFormOptions());
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $currentStage->handleFormResult($form);
                $params = [
                    'stage' => $wizard->getNextStage()->getName(),
                    '_locale' => $this->locale
                ];

                $url = $this->router->generate($mode, $params);

                return new RedirectResponse($url);
            }
            $templateParams['form'] = $form->createView();
        }

        return $this->renderResponse($currentStage->getTemplateName(), $templateParams);
    }

    private function renderResponse(string $view, array $parameters = [], Response $response = null): Response
    {
        if (null === $response) {
            $response = new PlainResponse();
        }
        $response->setContent($this->twig->render($view, $parameters));

        return $response;
    }

    private function getTemplateGlobals(StageInterface $currentStage): array
    {
        $globals = [
            'version' => ZikulaKernel::VERSION,
            'currentstage' => $currentStage->getName()
        ];

        return array_merge($globals, $currentStage->getTemplateParams());
    }
}
