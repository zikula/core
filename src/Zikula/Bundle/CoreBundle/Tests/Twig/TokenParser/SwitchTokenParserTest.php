<?php
namespace Zikula\Bundle\CoreBundle\Tests\Twig\TokenParser;

use Zikula\Bundle\CoreBundle\Twig\TokenParser\SwitchTokenParser;

class SwitchTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwitchTokenParser
     */
    protected $tokenParser;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->tokenParser = new SwitchTokenParser();
        $this->tokenParser->setParser(new \Twig_Parser(new \Twig_Environment()));
    }

    protected function tearDown()
    {
        $this->tokenParser = null;
    }

    /**
     * @covers Zikula\Bundle\CoreBundle\Twig\TokenParser\SwitchTokenParser::parse
     * @todo   Implement testParse().
     */
    public function testParse()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @dataProvider getDecideCaseFork
     */
    public function testDecideCaseFork($type, $value, $lineno, $boolean)
    {
        if (true === $boolean) {
            $this->assertTrue($this->tokenParser->decideCaseFork(new \Twig_Token($type, $value, $lineno)));
        } else {
            $this->assertFalse($this->tokenParser->decideCaseFork(new \Twig_Token($type, $value, $lineno)));
        }
    }

    public function getDecideCaseFork()
    {
        return array(
            array(\Twig_Token::NAME_TYPE, 'case', 1, true),
            array(\Twig_Token::NAME_TYPE, 'default', 1, true),
            array(\Twig_Token::NAME_TYPE, 'break', 1, true),
            array(\Twig_Token::NAME_TYPE, 'endswitch', 1, true),
            array(\Twig_Token::NAME_TYPE, 'casefoo', 1, false),
            array(\Twig_Token::NAME_TYPE, 'defaultfoo', 1, false),
            array(\Twig_Token::NAME_TYPE, 'breakfoo', 1, false),
            array(\Twig_Token::NAME_TYPE, 'endswithfoo', 1, false),
        );
    }

    /**
     * @covers Zikula\Bundle\CoreBundle\Twig\TokenParser\SwitchTokenParser::getTag
     * @todo   Implement testGetTag().
     */
    public function testGetTag()
    {
        $this->assertEquals('switch', $this->tokenParser->getTag());
    }
}
