<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Twig\TokenParser;

use Zikula\Bundle\CoreBundle\Twig\TokenParser\SwitchTokenParser;

class SwitchTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwitchTokenParser
     */
    protected $tokenParser;

    /**
     * @var \Twig_Parser
     */
    protected $twigParser;

    protected function setUp()
    {
        $this->tokenParser = new SwitchTokenParser();
        $this->twigParser = $this->getMockBuilder('Twig_Parser')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $this->tokenParser->setParser($this->twigParser);
    }

    protected function tearDown()
    {
        $this->twigParser = null;
        $this->tokenParser = null;
    }

    /**
     * @dataProvider getDecideCaseFork
     */
    public function testDecideCaseFork($type, $value, $lineno)
    {
        $this->assertTrue($this->tokenParser->decideCaseFork(new \Twig_Token($type, $value, $lineno)));
    }

    public function getDecideCaseFork()
    {
        return [
            [\Twig_Token::NAME_TYPE, 'case', 1],
            [\Twig_Token::NAME_TYPE, 'default', 1],
            [\Twig_Token::NAME_TYPE, 'break', 1],
            [\Twig_Token::NAME_TYPE, 'endswitch', 1],
        ];
    }

    public function testGetTag()
    {
        $this->assertEquals('switch', $this->tokenParser->getTag());
    }
}
