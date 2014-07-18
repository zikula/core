<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
  *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\SecurityCenterModule\Controller;

use SecurityUtil;
use ModUtil;
use System;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * form handler controllers for the security centre module
 */
class AdminformController extends \Zikula_AbstractController
{
    /**
     * Initialise.
     *
     * @return void
     */
    protected function initialize()
    {
        // Do not setup a view for this controller.
    }

    /**
     * Delete an ids log entry
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if the object id is not numeric or if
     */
    public function deleteidsentryAction()
    {
        // verify auth-key
        $csrftoken = $this->request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // get parameters
        $id = (int)$this->request->get('id', 0);

        // sanity check
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException($this->__f("Error! Received a non-numeric object ID '%s'.", $id));
        }

        $intrusion = $this->entityManager->find('ZikulaSecurityCenterModule:IntrusionEntity', $id);

        // check for valid object
        if (!$intrusion) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__f('Error! Invalid %s received.', "object ID [$id]"));
        } else {
            // delete object
            $this->entityManager->remove($intrusion);
            $this->entityManager->flush();
        }

        // redirect back to view function
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'viewidslog')));
    }
}
