<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Controller;

use SecurityUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove

/**
 * @Route("/adminform")
 *
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
     * @Route("/deleteidsentry")
     *
     * Delete an ids log entry
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if the object id is not numeric or if
     */
    public function deleteidsentryAction(Request $request)
    {
        // verify auth-key
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // get parameters
        $id = (int)$request->get('id', 0);

        // sanity check
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException($this->__f("Error! Received a non-numeric object ID '%s'.", $id));
        }

        $intrusion = $this->entityManager->find('ZikulaSecurityCenterModule:IntrusionEntity', $id);

        // check for valid object
        if (!$intrusion) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! Invalid %s received.', "object ID [$id]"));
        } else {
            // delete object
            $this->entityManager->remove($intrusion);
            $this->entityManager->flush();
        }

        return new RedirectResponse($this->get('router')->generate('zikulasecuritycentermodule_admin_viewidslog', array(), RouterInterface::ABSOLUTE_URL));
    }
}
