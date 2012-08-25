<?php
namespace Zikula\Bundle\CoreBundle\Tests\Twig\Node;

use Zikula\Bundle\CoreBundle\Twig\Node\SwitchNode;
use Zikula\Bundle\CoreBundle\Twig\Test\NodeTestCase;

class SwitchNodeTest extends NodeTestCase
{
    /**
     * @covers SwitchNode::__construct
     */
    public function testConstructor()
    {
        $expression = new \Twig_Node_Expression_Name('foo', 0);
        $default = null;
        $cases_array = array();
        $cases_array[] = new \Twig_Node(array(
            'expression' => new \Twig_Node_Expression_Constant(0, 0),
            'body' => new \Twig_Node_Text('case 0', 0)
        ));
        $cases_array[] = new \Twig_Node(array(
            'expression' => new \Twig_Node_Expression_Constant(1, 0),
            'body' => new \Twig_Node_Text('case 1', 0)
        ));
        $cases_array[1] ->setAttribute('break', true);

        $cases = new \Twig_Node($cases_array);

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
        $tests = array();

        // switch with one case, without break
        $expression = new \Twig_Node_Expression_Name('foo', 0);
        $default = null;
        $cases_array = array();
        $cases_array[0] = new \Twig_Node(array(
            'expression' => new \Twig_Node_Expression_Constant(0, 0),
            'body' => new \Twig_Node_Text('case 0', 0)
        ));

        $cases = new \Twig_Node($cases_array);

        $node = new SwitchNode($cases, $default, $expression, 0);

        $tests[] = array($node, <<<EOF
switch ({$this->getVariableGetter('foo')}) {
    case 0:
        echo "case 0";
}
EOF
        );

        // switch with two cases, second with break
        $cases_array[1] = new \Twig_Node(array(
            'expression' => new \Twig_Node_Expression_Constant(1, 0),
            'body' => new \Twig_Node_Text('case 1', 0)
        ));
        $cases_array[1] ->setAttribute('break', true);

        $cases = new \Twig_Node($cases_array);

        $node = new SwitchNode($cases, $default, $expression, 0);

        $tests[] = array($node, <<<EOF
switch ({$this->getVariableGetter('foo')}) {
    case 0:
        echo "case 0";
    case 1:
        echo "case 1";
        break;
}
EOF
        );

        // switch with two cases (second with break) and default
        $default = new \Twig_Node_Text('default case', 0);

        $node = new SwitchNode($cases, $default, $expression, 0);

        $tests[] = array($node, <<<EOF
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
        );

        return $tests;
    }
}
