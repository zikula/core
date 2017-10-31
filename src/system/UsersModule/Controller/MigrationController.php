<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;

/**
 * @deprecated remove at Core-2.0
 * @Route("/migration")
 */
class MigrationController extends AbstractController
{
    const BATCH_LIMIT = 25;

    /**
     * @deprecated
     * @Route("/migrate", options={"expose"=true, "i18n"=false})
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @return array|AjaxResponse
     */
    public function migrateAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $userRepo = $this->get('zikula_users_module.user_repository');
        if (!$request->isXmlHttpRequest()) {
            $count = $userRepo->count(['pass' => ['operator' => '!=', 'operand' => '']]);
            if (0 == $count) {
                throw new \LogicException($this->__('All users have been migrated already.'));
            }
            // set up
            $request->getSession()->set('user_migration_count', $count);
            $request->getSession()->set('user_migration_complete', 0);
            $request->getSession()->set('user_migration_lastuid', 0);
            $request->getSession()->set('user_migration_maxuid', $userRepo->getMaxUnMigratedUid());
        } else {
            $userEntities = $userRepo->getUnMigratedUsers($request->getSession()->get('user_migration_lastuid'), self::BATCH_LIMIT);
            foreach ($userEntities as $userEntity) {
                $mapping = $this->get('zikula_zauth_module.helper.migration_helper')->createMappingFromUser($userEntity);
                if ($mapping) {
                    $this->get('doctrine')->getManager()->persist($mapping);
                    $userEntity->setPass('');
                    $userEntity->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
                    $request->getSession()->set('user_migration_complete', $request->getSession()->get('user_migration_complete') + 1);
                }
                $request->getSession()->set('user_migration_lastuid', $userEntity->getUid());
            }
            $this->get('doctrine')->getManager()->flush();
            if ($request->getSession()->get('user_migration_lastuid') == $request->getSession()->get('user_migration_maxuid')) {
                $count = $request->getSession()->get('user_migration_count');
                $complete = $request->getSession()->get('user_migration_complete');
                if ($count == $complete) {
                    $this->addFlash('success', $this->__('All users migrated.'));
                } else {
                    $this->addFlash('warning', $this->__f('%complete of %count users migrated. Failed to migrate all due to validation errors. See the log for more information.',
                        ['%complete' => $complete, '%count' => $count]));
                }
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

        return ['count' => $count];
    }
}
