<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\PermissionsModule\Api\PermissionApi;

class GroupApplicationRepository extends EntityRepository
{
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @param PermissionApi $permissionApi
     */
    public function setPermissionApi(PermissionApi $permissionApi)
    {
        $this->permissionApi = $permissionApi;
    }

    public function getFilteredApplications()
    {
        $applications = parent::findBy([], ['app_id' => 'ASC']);

        $filteredApplications = [];
        /** @var GroupApplicationEntity $application */
        foreach ($applications as $application) {
            $group = $this->_em->getRepository('ZikulaGroupsModule:GroupEntity')->find($application->getGid());
            if (!$group) {
                continue;
            }

            if ($this->permissionApi->hasPermission('ZikulaGroupsModule::', $group->getGid() . '::', ACCESS_EDIT) && $group != false) {
                $user = $this->_em->getRepository('ZikulaUsersModule:UserEntity')->find($application->getUid());
                $filteredApplications[] = [
                    'app_id' => $application->getApp_id(),
                    'userid' => $application->getUid(),
                    'username' => $user->getUname(),
                    'appgid' => $application->getGid(),
                    'gname' => $group->getName(),
                    'application' => nl2br($application->getApplication()),
                    'status' => $application->getStatus()
                ];
            }
        }

        return $filteredApplications;
    }
}
