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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;

#[Route('/localization')]
#[PermissionCheck('admin')]
class LocalizationController extends AbstractController
{
    /**
     * Toggles the "Edit in place" translation functionality.
     */
    #[Route('/toggleeditinplace', name: 'zikulathemebundle_localization_toggleeditinplace')]
    public function toggleEditInPlace(Request $request, EditInPlaceActivator $activator): RedirectResponse
    {
        if ($request->hasSession() && ($session = $request->getSession())) {
            if ($session->has(EditInPlaceActivator::KEY)) {
                $activator->deactivate();
                $this->addFlash('status', 'Done! Disabled edit in place translations.');
            } else {
                $activator->activate();
                $this->addFlash('status', 'Done! Enabled edit in place translations.');
            }
        } else {
            $this->addFlash('error', 'Could not change the setting due to missing session access.');
        }

        return $this->redirectToRoute('zikulathemebundle_admin_view');
    }
}
