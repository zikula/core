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
    public function migrateAction(Request $request, MigrationHelper $migrationHelper): JsonResponse
    {
        $percentComplete = 0;
        if ($request->hasSession() && ($session = $request->getSession())) {
            if (!$session->has('user_migration_lastuid')) {
                $session->set('user_migration_count', $migrationHelper->countUnMigratedUsers());
                $session->set('user_migration_complete', 0);
                $session->set('user_migration_lastuid', 0);
                $session->set('user_migration_maxuid', $migrationHelper->getMaxUnMigratedUid());
            }
            $result = $migrationHelper->migrateUsers($session->get('user_migration_lastuid'));
            $session->set('user_migration_complete', $session->get('user_migration_complete') + $result['complete']);
            $session->set('user_migration_lastuid', $result['lastUid']);
            if ($session->get('user_migration_lastuid') === $session->get('user_migration_maxuid')) {
                $percentComplete = 100;
                // clean up
                $session->remove('user_migration_count');
                $session->remove('user_migration_complete');
                $session->remove('user_migration_lastuid');
                $session->remove('user_migration_maxuid');
            } else {
                $percentComplete = ceil(100 * $session->get('user_migration_complete') / $session->get('user_migration_count'));
            }
        }

        return new JsonResponse([
            'percentcomplete' => $percentComplete,
        ]);
    }
}
