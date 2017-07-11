<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Response\Ajax\AjaxResponse;

class MigrationController extends AbstractController
{
    /**
     * @param Request $request
     * @return AjaxResponse
     */
    public function migrateAction(Request $request)
    {
        if (!$request->getSession()->has('user_migration_lastuid')) {
            throw new \InvalidArgumentException('user_migration_lastuid is not set in the Session!');
        }
        $result = $this->container
            ->get('zikula_core_installer.helper.migration_helper')
            ->migrateUsers($request->getSession()->get('user_migration_lastuid'));
        $request->getSession()->set('user_migration_complete', $request->getSession()->get('user_migration_complete') + $result['complete']);
        $request->getSession()->set('user_migration_lastuid', $result['lastUid']);
        if ($request->getSession()->get('user_migration_lastuid') == $request->getSession()->get('user_migration_maxuid')) {
            $percentComplete = 100;
            // clean up
            $request->getSession()->remove('user_migration_count');
            $request->getSession()->remove('user_migration_complete');
            $request->getSession()->remove('user_migration_lastuid');
            $request->getSession()->remove('user_migration_maxuid');
        } else {
            $percentComplete = ceil(100 * $request->getSession()->get('user_migration_complete') / $request->getSession()->get('user_migration_count'));
        }

        return new AjaxResponse([
            'percentcomplete' => $percentComplete,
        ]);
    }
}
