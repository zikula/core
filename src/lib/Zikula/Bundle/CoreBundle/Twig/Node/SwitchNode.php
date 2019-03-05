<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

class SwitchNode extends Node
{
    public function __construct(Node $cases, Node $default = null, AbstractExpression $expression, $lineno, $tag = null)
    {
        $nodes = [
            'cases' => $cases,
            'default' => $default,
            'expression' => $expression
        ];
        parent::__construct($nodes, [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write('switch (')
            ->subcompile($this->getNode('expression'))
            ->raw(") {\n")
            ->indent();

        /* @var $case Node */
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
            if ($case->hasAttribute('break') && true === $case->getAttribute('break')) {
                $compiler
                    ->write("break;\n");
            }
            $compiler->outdent();
        }

        if ($this->hasNode('default') && null !== $this->getNode('default')) {
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
