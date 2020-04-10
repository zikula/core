<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Twig\TokenParser;

use PHPUnit\Framework\TestCase;
use Twig\Parser;
use Twig\Token;
use Zikula\Bundle\CoreBundle\Twig\TokenParser\SwitchTokenParser;

class SwitchTokenParserTest extends TestCase
{
    /**
     * @var SwitchTokenParser
     */
    protected $tokenParser;

    /**
     * @var Parser
     */
    protected $twigParser;

    protected function setUp(): void
    {
        $this->tokenParser = new SwitchTokenParser();
        $this->twigParser = $this->getMockBuilder(Parser::class)
             ->disableOriginalConstructor()
             ->getMock();
        $this->tokenParser->setParser($this->twigParser);
    }

    protected function tearDown(): void
    {
        $this->twigParser = null;
        $this->tokenParser = null;
    }

    /**
     * @dataProvider getDecideCaseFork
     */
    public function testDecideCaseFork(int $type, string $value, int $lineno): void
    {
        $this->assertTrue($this->tokenParser->decideCaseFork(new Token($type, $value, $lineno)));
    }

    public function getDecideCaseFork(): array
    {
        return [
            [Token::NAME_TYPE, 'case', 1],
            [Token::NAME_TYPE, 'default', 1],
            [Token::NAME_TYPE, 'break', 1],
            [Token::NAME_TYPE, 'endswitch', 1]
        ];
    }

    public function testGetTag(): void
    {
        $this->assertEquals('switch', $this->tokenParser->getTag());
    }
}
