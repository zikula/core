<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\TokenParser;

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
class SwitchTokenParser extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();

        $expression = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        $this->parser->subparse(array($this, 'decideCaseFork'));
        $cases = new \Twig_Node();
        $default = null;

        $end = false;
        $i = 0;
        while (!$end) {
            switch ($tag = $this->parser->getStream()->next()->getValue()) {
                case 'case':
                    $i++;
                    $expr = $this->parser->getExpressionParser()->parseExpression();
                    $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse(array($this, 'decideCaseFork'));

                    $cases->setNode($i, new \Twig_Node(array(
                        'expression' => $expr,
                        'body' => $body,
                    )));

                    break;

                case 'default':
                    $i = null;
                    $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse(array($this, 'decideCaseFork'));

                    $default = $body;

                    break;

                case 'break':
                    $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
                    $this->parser->subparse(array($this, 'decideCaseFork'));

                    if ($cases->hasNode($i)) {
                        $cases->getNode($i)->setAttribute('break', true);
                    }

                    break;

                case 'endswitch':
                    $end = true;
                    break;

                default:
                    throw new \Twig_Error_Syntax(sprintf('Unexpected end of template at line %d' . $tag, $lineno), -1);
            }
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new SwitchNode($cases, $default, $expression, $lineno, $this->getTag());
    }

    public function decideCaseFork(\Twig_Token $token)
    {
        return $token->test(array('case', 'default', 'break', 'endswitch'));
    }

    public function getTag()
    {
        return 'switch';
    }
}
