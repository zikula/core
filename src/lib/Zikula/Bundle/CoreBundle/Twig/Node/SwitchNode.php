<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Node;

class SwitchNode extends \Twig_Node
{
    public function __construct(\Twig_Node $cases, \Twig_Node $default = null, \Twig_Node_Expression $expression, $lineno, $tag = null)
    {
        $nodes = [
            'cases' => $cases,
            'default' => $default,
            'expression' => $expression
        ];
        parent::__construct($nodes, [], $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write('switch (')
            ->subcompile($this->getNode('expression'))
            ->raw(") {\n")
            ->indent();

        /* @var $case \Twig_Node */
        foreach ($this->getNode('cases')->getIterator() as $key => $case) {
            $compiler
                ->write('case ')
                ->subcompile($case->getNode('expression'))
                ->raw(":\n");
            if ($case->hasNode('body')) {
                $compiler
                    ->indent()
                    ->subcompile($case->getNode('body'));
            }
            if ($case->hasAttribute('break') && $case->getAttribute('break') == true) {
                $compiler
                    ->write("break;\n");
            }
            $compiler->outdent();
        }

        if ($this->hasNode('default') && $this->getNode('default') !== null) {
            $compiler
                ->write('default')
                ->raw(":\n")
                ->indent()
                ->subcompile($this->getNode('default'))
                ->outdent();
        }

        $compiler
            ->outdent()
            ->write("}\n");
    }
}
