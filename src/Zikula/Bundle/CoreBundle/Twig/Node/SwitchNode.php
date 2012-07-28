<?php

namespace Zikula\Bundle\CoreBundle\Twig\Node;

use Zikula\Bundle\CoreBundle\Twig;

class SwitchNode extends \Twig_Node
{
    public function __construct($cases, \Twig_Node_Expression $value, $lineno, $tag = null)
    {
        parent::__construct(array('value' => $value), array('cases' => $cases), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write('switch (')
            ->subcompile($this->getNode('value'))
            ->raw(") {\n")
            ->indent();

        foreach ((array)$this->getAttribute('cases') as $key => $case) {
            if ($key === 'default') {
                $compiler
                    ->write('default');
            } else {
                $compiler
                    ->write('case ')
                    ->subcompile($case['expression']);
            }
            $compiler
                ->raw(":\n")
                ->indent()
                ->subcompile($case['body']);
            if (isset($case['break'])) {
                $compiler
                    ->write("break;\n");
            }
            $compiler->outdent();
        }
        $compiler
            ->outdent()
            ->raw("}\n");
    }
}