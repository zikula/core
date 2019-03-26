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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreInstallerBundle\Helper\MigrationHelper;

class MigrationController extends AbstractController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function migrateAction(Request $request, MigrationHelper $migrationHelper)
    {
        if (!$request->getSession()->has('user_migration_lastuid')) {
            $request->getSession()->set('user_migration_count', $migrationHelper->countUnMigratedUsers());
            $request->getSession()->set('user_migration_complete', 0);
            $request->getSession()->set('user_migration_lastuid', 0);
            $request->getSession()->set('user_migration_maxuid', $migrationHelper->getMaxUnMigratedUid());
        }
        $result = $migrationHelper->migrateUsers($request->getSession()->get('user_migration_lastuid'));
        $request->getSession()->set('user_migration_complete', $request->getSession()->get('user_migration_complete') + $result['complete']);
        $request->getSession()->set('user_migration_lastuid', $result['lastUid']);
        if ($request->getSession()->get('user_migration_lastuid') === $request->getSession()->get('user_migration_maxuid')) {
            $percentComplete = 100;
            // clean up
            $request->getSession()->remove('user_migration_count');
            $request->getSession()->remove('user_migration_complete');
            $request->getSession()->remove('user_migration_lastuid');
            $request->getSession()->remove('user_migration_maxuid');
        } else {
            $percentComplete = ceil(100 * $request->getSession()->get('user_migration_complete') / $request->getSession()->get('user_migration_count'));
        }

        return $this->json([
            'percentcomplete' => $percentComplete,
        ]);
    }
}
