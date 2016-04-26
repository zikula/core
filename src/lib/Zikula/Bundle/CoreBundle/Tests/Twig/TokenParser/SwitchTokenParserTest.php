<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
            [\Twig_Token::NAME_TYPE, 'endswitch', 1]
        ];
    }

    public function testGetTag()
    {
        $this->assertEquals('switch', $this->tokenParser->getTag());
    }
}
