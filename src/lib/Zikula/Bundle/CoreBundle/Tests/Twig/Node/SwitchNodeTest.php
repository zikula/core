<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Twig\Node;

use Zikula\Bundle\CoreBundle\Twig\Node\SwitchNode;

class SwitchNodeTest extends \Twig_Test_NodeTestCase
{
    /**
     * @covers \SwitchNode::__construct
     */
    public function testConstructor()
    {
        $expression = new \Twig_Node_Expression_Name('foo', 0);
        $default = null;
        $cases = new \Twig_Node();
        $cases->setNode(0, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(0, 0),
            'body' => new \Twig_Node_Text('case 0', 0)
        ]));
        $cases->setNode(1, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(1, 0),
            'body' => new \Twig_Node_Text('case 1', 0)
        ]));
        $cases->getNode(1)->setAttribute('break', true);

        $node = new SwitchNode($cases, $default, $expression, 0);

        $this->assertEquals($expression, $node->getNode('expression'));
        $this->assertEquals($default, $node->getNode('default'));
        $this->assertEquals($cases, $node->getNode('cases'));

        $default = new \Twig_Node_Text('default case', 0);
        $node = new SwitchNode($cases, $default, $expression, 0);
        $this->assertEquals($default, $node->getNode('default'));
    }

    public function getTests()
    {
        $tests = [];

        // #1 switch with one case, without break
        $expression = new \Twig_Node_Expression_Name('foo', 0);
        $default = null;
        $cases = new \Twig_Node();
        $cases->setNode(0, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(0, 0),
            'body' => new \Twig_Node_Text('case 0', 0)
        ]));
        $node = new SwitchNode($cases, $default, $expression, 0);

        $tests[] = [$node, <<<EOF
switch ({$this->getVariableGetter('foo')}) {
    case 0:
        echo "case 0";
}
EOF
        ];

        // #2 switch with two cases, second with break
        $expression = new \Twig_Node_Expression_Name('foo', 0);
        $default = null;
        $cases = new \Twig_Node();
        $cases->setNode(0, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(0, 0),
            'body' => new \Twig_Node_Text('case 0', 0)
        ]));
        $cases->setNode(1, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(1, 0),
            'body' => new \Twig_Node_Text('case 1', 0)
        ]));
        $cases->getNode(1)->setAttribute('break', true);
        $node = new SwitchNode($cases, $default, $expression, 0);

        $tests[] = [$node, <<<EOF
switch ({$this->getVariableGetter('foo')}) {
    case 0:
        echo "case 0";
    case 1:
        echo "case 1";
        break;
}
EOF
        ];

        // #3 switch with two cases (second with break) and default
        $expression = new \Twig_Node_Expression_Name('foo', 0);
        $default = new \Twig_Node_Text('default case', 0);
        $cases = new \Twig_Node();
        $cases->setNode(0, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(0, 0),
            'body' => new \Twig_Node_Text('case 0', 0)
        ]));
        $cases->setNode(1, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(1, 0),
            'body' => new \Twig_Node_Text('case 1', 0)
        ]));
        $cases->getNode(1)->setAttribute('break', true);
        $node = new SwitchNode($cases, $default, $expression, 0);

        $tests[] = [$node, <<<EOF
switch ({$this->getVariableGetter('foo')}) {
    case 0:
        echo "case 0";
    case 1:
        echo "case 1";
        break;
    default:
        echo "default case";
}
EOF
        ];

        // #4 switch with two cases (first without body, second with break) and default
        $expression = new \Twig_Node_Expression_Name('foo', 0);
        $default = new \Twig_Node_Text('default case', 0);
        $cases = new \Twig_Node();
        $cases->setNode(0, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(0, 0),
            'body' => new \Twig_Node()
        ]));
        $cases->setNode(1, new \Twig_Node([
            'expression' => new \Twig_Node_Expression_Constant(1, 0),
            'body' => new \Twig_Node_Text('case 1', 0)
        ]));
        $cases->getNode(1)->setAttribute('break', true);
        $node = new SwitchNode($cases, $default, $expression, 0);

        $tests[] = [$node, <<<EOF
switch ({$this->getVariableGetter('foo')}) {
    case 0:
    case 1:
        echo "case 1";
        break;
    default:
        echo "default case";
}
EOF
        ];

        return $tests;
    }
}
