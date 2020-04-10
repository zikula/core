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

namespace Zikula\Bundle\CoreBundle\Twig\TokenParser;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Zikula\Bundle\CoreBundle\Twig\Node\SwitchNode;

/**
 * @example
 *  {% switch variable %}
 *      {% case val_1 %}
 *          code for val_1
 *          (notice - here's not break)
 *      {% case val_2 %}
 *          code for val_2
 *          {% break %}
 *      {% default %}
 *          code for default case
 * {% endswitch %}
 */
class SwitchTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $lineno = $token->getLine();

        $expression = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $this->parser->subparse([$this, 'decideCaseFork']);
        $cases = new Node();
        $default = null;

        $end = false;
        $i = 0;
        while (!$end) {
            switch ($tag = $this->parser->getStream()->next()->getValue()) {
                case 'case':
                    $i++;
                    $expr = $this->parser->getExpressionParser()->parseExpression();
                    $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse([$this, 'decideCaseFork']);

                    $cases->setNode((string)$i, new Node([
                        'expression' => $expr,
                        'body' => $body,
                    ]));

                    break;

                case 'default':
                    $i = null;
                    $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse([$this, 'decideCaseFork']);

                    $default = $body;

                    break;

                case 'break':
                    $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
                    $this->parser->subparse([$this, 'decideCaseFork']);

                    if ($cases->hasNode((string)$i)) {
                        $cases->getNode((string)$i)->setAttribute('break', true);
                    }

                    break;

                case 'endswitch':
                    $end = true;
                    break;

                default:
                    throw new SyntaxError(sprintf('Unexpected end of template at line %d' . $tag, $lineno), -1);
            }
        }

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new SwitchNode($cases, $default, $expression, $lineno, $this->getTag());
    }

    public function decideCaseFork(Token $token): bool
    {
        return $token->test(['case', 'default', 'break', 'endswitch']);
    }

    public function getTag(): string
    {
        return 'switch';
    }
}
