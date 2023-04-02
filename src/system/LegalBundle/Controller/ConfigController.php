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

namespace Zikula\LegalBundle\Controller;

use Nucleos\UserBundle\Model\GroupManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\LegalBundle\Form\Type\ConfigType;
use Zikula\LegalBundle\Helper\ResetAgreementHelper;

#[Route('/legal')]
#[IsGranted('ROLE_ADMIN')]
class ConfigController extends AbstractController
{
    public function __construct(
        private readonly GroupManager $groupManager,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/config', name: 'zikulalegalbundle_config_config')]
    public function config(
        Request $request,
        ResetAgreementHelper $resetAgreementHelper
    ): Response {
        // build choices for user group selector
        $groupChoices = [
            $this->translator->trans('All users') => 0,
        ];
        $groups = $this->groupManager->findGroups();
        foreach ($groups as $group) {
            $groupChoices[$group->getName()] = $group->getId();
        }

        $form = $this->createForm(ConfigType::class, [], [
            'groupChoices' => $groupChoices,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $resetAgreementGroupId = -1;
                if (isset($formData['resetagreement'])) {
                    $resetAgreementGroupId = $formData['resetagreement'];
                    unset($formData['resetagreement']);
                }

                if (-1 !== $resetAgreementGroupId) {
                    $resetAgreementHelper->reset($resetAgreementGroupId);
                }

                $this->addFlash('status', 'Done! Configuration updated.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            // redirecting prevents values from being repeated in the form
            return $this->redirectToRoute('zikulalegalbundle_config_config');
        }

        return $this->render('@ZikulaLegal/Config/config.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
