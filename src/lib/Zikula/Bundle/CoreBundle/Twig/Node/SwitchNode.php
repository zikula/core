<?php

namespace Zikula\Bundle\CoreBundle\Twig\Node;

class SwitchNode extends \Twig_Node
{
    public function __construct(\Twig_NodeInterface $cases, \Twig_NodeInterface $default = null, \Twig_Node_Expression $expression, $lineno, $tag = null)
    {
        $nodes = array(
            'cases' => $cases,
            'default' => $default,
            'expression' => $expression
        );
        parent::__construct($nodes, array(), $lineno, $tag);
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
