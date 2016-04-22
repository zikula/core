<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Core\Controller\AbstractController;

/**
 * Class AdminController
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * The main administration function.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulasearchmodule_admin_index route is deprecated. please use zikulasearchmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasearchmodule_admin_modifyconfig');
    }
}
