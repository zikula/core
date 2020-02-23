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

namespace Zikula\PermissionsModule\Tests\Api;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Request;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Listener\ControllerPermissionCheckAnnotationReaderListener;
use Zikula\PermissionsModule\Tests\Api\Fixtures\BarController;
use Zikula\PermissionsModule\Tests\Api\Fixtures\FailController;
use Zikula\PermissionsModule\Tests\Api\Fixtures\FooController;

class CheckPermissionAnnotationTest extends AbstractPermissionTestCase
{
    /**
     * Call protected/private method of the listener class.
     *
     * @return mixed Method return
     * @throws \ReflectionException
     */
    private function invokeMethod(ControllerPermissionCheckAnnotationReaderListener $listener, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($listener));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($listener, $parameters);
    }

    private function getListener()
    {
        $listener = new ControllerPermissionCheckAnnotationReaderListener(
            new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator),
            new AnnotationReader()
        );

        return $listener;
    }

    public function testGetConstant()
    {
        $listener = $this->getListener();
        $this->assertEquals(ACCESS_ADMIN, $this->invokeMethod($listener, 'getConstant', ['admin']));
        $this->assertEquals(ACCESS_ADMIN, $this->invokeMethod($listener, 'getConstant', ['ACCESS_ADMIN']));
        $this->expectException(AnnotationException::class);
        $this->invokeMethod($listener, 'getConstant', ['foo']);
        $this->expectException(AnnotationException::class);
        $this->invokeMethod($listener, 'getConstant', [ACCESS_ADMIN]);
    }

    public function testHasFlag()
    {
        $listener = $this->getListener();
        $this->assertTrue($this->invokeMethod($listener, 'hasFlag', ['$foo']));
        $this->assertFalse($this->invokeMethod($listener, 'hasFlag', ['foo']));
    }

    /**
     * @dataProvider replaceableSchemaProvider
     */
    public function testReplaceRouteAttributes(string $expected, string $schema, array $attributes)
    {
        $listener = $this->getListener();
        $request = new Request([], [], $attributes);
        $this->assertEquals($expected, $this->invokeMethod($listener, 'replaceRouteAttributes', [$schema, $request]));
    }

    public function replaceableSchemaProvider(): array
    {
        return [
            ['5::', '$gid::', ['gid' => 5]],
            ['::5', '::$gid', ['gid' => 5]],
            [':5:', ':$gid:', ['gid' => 5]],
            ['gid$::', 'gid$::', ['gid' => 5]],
            ['gid$gid::', 'gid$gid::', ['gid' => 5]],
            [':gid$gid:5', ':gid$gid:$gid', ['gid' => 5]],
            ['AcmeFooModule::', '$_zkModule::', ['_zkModule' => 'AcmeFooModule']],
            ['AcmeFooModule:gid:5', '$_zkModule:gid:$gid', ['gid' => 5, '_zkModule' => 'AcmeFooModule']],
        ];
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testIsValidSchema(bool $expected, array $schema)
    {
        $listener = $this->getListener();
        $this->assertEquals($expected, $this->invokeMethod($listener, 'isValidSchema', [$schema]));
    }

    public function schemaProvider(): array
    {
        return [
            [true, ['::', '::', 'admin']],
            [true, ['.*', '.*', 'ACCESS_OVERVIEW']],
            [true, ['::', ':(1|2|3):', 'admin']],
            [true, ['$foo::', '::$bar', 'edit']],
            [false, ['::', 'admin']],
            [false, ['::', '::', '::', 'admin']],
            [false, [1, '::', 'admin']],
            [false, [true, '::', 'admin']],
            [false, ['::', '::', ['foo']]],
        ];
    }

    /**
     * @dataProvider checkProvider
     */
    public function testFormatSchema(array $expected, array $data)
    {
        $listener = $this->getListener();
        $request = new Request([], [], ['gid' => 5, 'foo' => 'bar', '_zkModule' => 'AcmeFooModule']);
        $annot = new PermissionCheck($data);
        $this->assertEquals($expected, $this->invokeMethod($listener, 'formatSchema', [$annot, $request]));
    }

    public function checkProvider(): array
    {
        return [
            [['AcmeFooModule::', '::', ACCESS_ADMIN], ['value' => 'admin']],
            [['AcmeFooModule::', '::', ACCESS_ADMIN], ['value' => ['$_zkModule::', '::', 'admin']]],
            [['AcmeFooModule::', '::5', ACCESS_ADMIN], ['value' => ['$_zkModule::', '::$gid', 'admin']]],
            [['::', '::gid$gid', ACCESS_ADMIN], ['value' => ['::', '::gid$gid', 'admin']]],
            [['::', '5:5:5', ACCESS_ADMIN], ['value' => ['::', '$gid:$gid:$gid', 'admin']]],
            [['::', 'bar:$bar:5', ACCESS_ADMIN], ['value' => ['::', '$foo:$bar:$gid', 'admin']]],
            [['AcmeFooModule::bar', 'bar:bar:5', ACCESS_EDIT], ['value' => ['$_zkModule::$foo', '$foo:bar:$gid', 'ACCESS_EDIT']]],
        ];
    }

    /**
     * @dataProvider controllerProvider
     */
    public function testGetAnnotationValueFromController($expected, array $controller)
    {
        $listener = $this->getListener();
        if ($expected instanceof PermissionCheck) {
            $this->assertEquals($expected, $this->invokeMethod($listener, 'getAnnotationValueFromController', [$controller]));
        } else {
            $this->expectException($expected);
            $this->invokeMethod($listener, 'getAnnotationValueFromController', [$controller]);
        }
    }

    public function controllerProvider(): array
    {
        return [
            [new PermissionCheck(['value' => 'admin']), [new FooController(), 'firstAction']],
            [new PermissionCheck(['value' => ['AcmeFooModule::', '.*', 'overview']]), [new FooController(), 'secondAction']],
            [new PermissionCheck(['value' => ['$_zkModule', '::$gid', 'ACCESS_EDIT']]), [new FooController(), 'thirdAction']],
            [new PermissionCheck(['value' => 'admin']), [new FooController(), 'fourthAction']], // duplicates
            [new PermissionCheck(['value' => 'admin']), [new BarController(), 'firstAction']],
            [AnnotationException::class, [new FailController(), 'firstAction']],
        ];
    }
}
