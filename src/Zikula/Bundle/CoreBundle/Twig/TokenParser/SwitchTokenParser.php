<?php

namespace Zikula\Bundle\CoreBundle\Twig\TokenParser;

use Zikula\Bundle\CoreBundle\Twig;

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
 *
 */
class SwitchTokenParser extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();

        $value = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        $this->parser->subparse(array($this, 'decideCaseFork'));
        $cases = array();

        $end = false;
        $i = 0;
        while (!$end) {
            switch ($tag = $this->parser->getStream()->next()->getValue()) {
                case 'case':
                    $i++;
                    $expr = $this->parser->getExpressionParser()->parseExpression();
                    $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse(array($this, 'decideCaseFork'));

                    $cases[$i] = array(
                        'expression' => $expr,
                        'body' => $body,
                    );

                    break;

                case 'default':
                    $i = null;
                    $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse(array($this, 'decideCaseFork'));

                    $cases['default'] = array(
                        'body' => $body,
                    );

                    break;

                case 'break':
                    $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
                    $this->parser->subparse(array($this, 'decideCaseFork'));
                    if (!is_null($i)) {
                        $cases[$i]['break'] = true;
                    }
                    break;

                case 'endcase':
                case 'enddefault':
                    $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
                    $this->parser->subparse(array($this, 'decideCaseFork'));
                    break;

                case 'endswitch':
                    $end = true;
                    break;

                default:
                    throw new \Twig_Error_Syntax(sprintf('Unexpected end of template at line %d' . $tag, $lineno), -1);
            }
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new Twig\Node\SwitchNode($cases, $value, $lineno, $this->getTag());
    }

    public function decideCaseFork(\Twig_Token $token)
    {
        return $token->test(array('case', 'endcase', 'default', 'enddefault', 'break', 'endswitch'));
    }

    public function getTag()
    {
        return 'switch';
    }
}