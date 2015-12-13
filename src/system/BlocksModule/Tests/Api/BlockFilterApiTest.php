<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Tests\Api;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Zikula\BlocksModule\Api\BlockFilterApi;

class BlockFilterApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockFilterApi
     */
    private $api;

    public function setUp()
    {
        $request = new Request([
            // query params
            'q-a' => 1,
            'q-b' => 2
        ], [], [
            // attributes
            'foo' => 'bar',
            'fee' => 'bee',
            'fii' => 'bii',
            'int' => 9,
            '_route_params' => [
                'zee' => 'zar'
            ]
        ]);
        $request->setLocale('en');
        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();
        $requestStack
            ->method('getCurrentRequest')
            ->willReturn($request);
        $this->api = new BlockFilterApi($requestStack);
    }

    /**
     * @covers BlockFilterApi::getFilterAttributeChoices
     */
    public function testGetFilterAttributeChoices()
    {
        $expected = [
            'foo' => 'foo',
            'fee' => 'fee',
            'fii' => 'fii',
            'int' => 'int',
            '_route_params' => '_route_params',
            'query param' => 'query param'
        ];
        $this->assertEquals($expected, $this->api->getFilterAttributeChoices());
    }

    /**
     * @covers BlockFilterApi::isDisplayable
     * @dataProvider filterProvider
     * @param array $filter
     * @param bool $expected
     */
    public function testIsDisplayable($filter, $expected)
    {
        $blockEntity = $this->getMockBuilder('Zikula\BlocksModule\Entity\BlockEntity')
            ->getMock();
        $blockEntity
            ->method('getLanguage')
            ->willReturn('en');
        $blockEntity
            ->method('getFilters')
            ->willReturn($filter);
        $this->assertEquals($expected, $this->api->isDisplayable($blockEntity));
    }

    public function filterProvider()
    {
        return [
            [[], true],
            [[[
                'attribute' => 'foo',
                'comparator' => '==',
                'value' => 'bar'
            ]], true],
            [[[
                'attribute' => 'foo',
                'comparator' => '==',
                'value' => 'bee'
            ]], false],
            [[[
                'attribute' => 'foo',
                'comparator' => '!=',
                'value' => 'bee'
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => '==',
                'value' => 9
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => '>=',
                'value' => 9
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => '<=',
                'value' => 9
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => '>',
                'value' => 8
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => '<',
                'value' => 10
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => 'in_array',
                'value' => "8,9,10"
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => '!in_array',
                'value' => "10,11,12"
            ]], true],
            [[[
                'attribute' => 'int',
                'comparator' => 'in_array',
                'value' => " 8,9     ,     10   "
            ]], true],
            [[
                [
                    'attribute' => 'foo',
                    'comparator' => '==',
                    'value' => 'bar'
                ],
                [
                    'attribute' => 'fee',
                    'comparator' => '==',
                    'value' => 'bee'
                ],
            ], true],
            [[
                [
                    'attribute' => 'foo',
                    'comparator' => '==',
                    'value' => 'bar'
                ],
                [
                    'attribute' => 'fee',
                    'comparator' => 'in_array',
                    'value' => ',baz,bee,bum'
                ],
                [
                    'attribute' => 'int',
                    'comparator' => '>=',
                    'value' => '3'
                ],
            ], true],
            [[
                [
                    'attribute' => 'query param',
                    'queryParameter' => 'q-a',
                    'comparator' => '==',
                    'value' => '1'
                ]
            ], true],
            [[
                [
                    'attribute' => 'query param',
                    'queryParameter' => 'q-b',
                    'comparator' => '==',
                    'value' => '2'
                ]
            ], true],
            [[
                [
                    'attribute' => 'q-b',
                    'comparator' => '==',
                    'value' => '2'
                ]
            ], false],
            [[
                [
                    'attribute' => '_route_params',
                    'queryParameter' => 'zee',
                    'comparator' => '==',
                    'value' => 'zar'
                ]
            ], true],
            [[
                [
                    'attribute' => '_route_params',
                    'queryParameter' => 'foo',
                    'comparator' => '==',
                    'value' => 'zar'
                ]
            ], false],
        ];
    }
}
