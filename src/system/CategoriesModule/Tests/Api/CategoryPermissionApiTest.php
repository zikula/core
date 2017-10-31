<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Tests\Api;

use Zikula\CategoriesModule\Api\CategoryPermissionApi;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Tests\Fixtures\CategorizableEntity;
use Zikula\CategoriesModule\Tests\Fixtures\CategoryAssignmentEntity;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\PermissionsModule\PermissionAlways;

class CategoryPermissionApiTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $permissionApi = new PermissionAlways();
        $api = new CategoryPermissionApi($permissionApi);
        $this->assertTrue($api->hasCategoryAccess([]));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidDataThrowsException()
    {
        $permissionApi = new PermissionAlways();
        $api = new CategoryPermissionApi($permissionApi);
        $category = new CategoryEntity();
        $api->hasCategoryAccess([$category]);
        $api->hasCategoryAccess([['foo' => 'bar']]);
    }

    public function testValidDataAlwaysWorks()
    {
        $permissionApi = new PermissionAlways();
        $api = new CategoryPermissionApi($permissionApi);
        $catAssignment = new CategoryAssignmentEntity(1, new CategoryEntity(), new CategorizableEntity());
        $this->assertTrue($api->hasCategoryAccess([$catAssignment]));
    }

    public function testSingleData()
    {
        $permissionApi = $this->createEvenPermissionApi();
        $api = new CategoryPermissionApi($permissionApi);
        $categorizableEntity = new CategorizableEntity();
        $category = new CategoryEntity();

        $category->setId(1);
        $catAssignment = new CategoryAssignmentEntity(1, $category, $categorizableEntity);
        $this->assertFalse($api->hasCategoryAccess([$catAssignment]));

        $category->setId(2);
        $catAssignment = new CategoryAssignmentEntity(1, $category, $categorizableEntity);
        $this->assertTrue($api->hasCategoryAccess([$catAssignment]));
    }

    public function testMultipleData()
    {
        $permissionApi = $this->createEvenPermissionApi();
        $api = new CategoryPermissionApi($permissionApi);
        $categorizableEntity = new CategorizableEntity();
        $category = new CategoryEntity();
        $category->setId(1);
        $category2 = new CategoryEntity();
        $category2->setId(2);

        $catAssignment = new CategoryAssignmentEntity(1, $category, $categorizableEntity);
        $catAssignment2 = new CategoryAssignmentEntity(1, $category2, $categorizableEntity);
        $this->assertTrue($api->hasCategoryAccess([$catAssignment, $catAssignment2]));
    }

    public function testMultipleDataRequireAll()
    {
        $permissionApi = $this->createEvenPermissionApi();
        $api = new CategoryPermissionApi($permissionApi);
        $categorizableEntity = new CategorizableEntity();
        $category = new CategoryEntity();
        $category->setId(1);
        $category2 = new CategoryEntity();
        $category2->setId(2);

        $catAssignment = new CategoryAssignmentEntity(1, $category, $categorizableEntity);
        $catAssignment2 = new CategoryAssignmentEntity(1, $category2, $categorizableEntity);
        $this->assertFalse($api->hasCategoryAccess([$catAssignment, $catAssignment2], ACCESS_OVERVIEW, true));
        $category->setId(4);
        $this->assertTrue($api->hasCategoryAccess([$catAssignment, $catAssignment2], ACCESS_OVERVIEW, true));
    }

    /**
     * Instances where final number is even will have permission (e.g. '1::2' is true, but '1::3' is false)
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createEvenPermissionApi()
    {
        $api = $this->getMockBuilder(PermissionApiInterface::class)
            ->getMock();
        $api->method('hasPermission')->will($this->returnCallback(
            function($component = null, $instance = null, $level = ACCESS_NONE, $user = null) {
                list($regId, $catId) = explode('::', $instance);

                return 0 == $catId % 2;
            }
        ));

        return $api;
    }
}
