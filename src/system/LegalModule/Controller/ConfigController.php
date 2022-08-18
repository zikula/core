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

namespace Zikula\LegalModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\GroupsModule\Repository\GroupRepositoryInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Form\Type\ConfigType;
use Zikula\LegalModule\Helper\ResetAgreementHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Template("@ZikulaLegalModule/Config/config.html.twig")
     * @Theme("admin")
     *
     * @return array|RedirectResponse
     */
    public function config(
        Request $request,
        GroupRepositoryInterface $groupRepository,
        ResetAgreementHelper $resetAgreementHelper
    ) {
        $booleanVars = [
            LegalConstant::MODVAR_LEGALNOTICE_ACTIVE,
            LegalConstant::MODVAR_TERMS_ACTIVE,
            LegalConstant::MODVAR_PRIVACY_ACTIVE,
            LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE,
            LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE,
            LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE,
        ];

        $dataValues = $this->getVars();
        foreach ($booleanVars as $booleanVar) {
            $dataValues[$booleanVar] = (bool) $dataValues[$booleanVar];
        }

        // build choices for user group selector
        $groupChoices = [
            $this->trans('All users') => 0,
        ];

        // get all user groups
        $groups = $groupRepository->findAll();
        foreach ($groups as $group) {
            $groupChoices[$group->getName()] = $group->getGid();
        }

        $form = $this->createForm(ConfigType::class, $dataValues, [
            'groupChoices' => $groupChoices,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                foreach ($booleanVars as $booleanVar) {
                    $formData[$booleanVar] = (true === $formData[$booleanVar] ? 1 : 0);
                }

                $resetAgreementGroupId = -1;
                if (isset($formData['resetagreement'])) {
                    $resetAgreementGroupId = $formData['resetagreement'];
                    unset($formData['resetagreement']);
                }

                // save modvars
                $this->setVars($formData);

                if (-1 !== $resetAgreementGroupId) {
                    $resetAgreementHelper->reset($resetAgreementGroupId);
                }

                $this->addFlash('status', 'Done! Configuration updated.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            // redirecting prevents values from being repeated in the form
            return $this->redirectToRoute('zikulalegalmodule_config_config');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
