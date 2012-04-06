<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Definition\Builder;

use Symfony\Component\Config\Definition\NodeInterface;

/**
 * This class provides a fluent interface for defining a node.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class NodeDefinition implements NodeParentInterface
{
    protected $name;
    protected $normalization;
    protected $validation;
    protected $defaultValue;
    protected $default;
    protected $required;
    protected $merge;
    protected $allowEmptyValue;
    protected $nullEquivalent;
    protected $trueEquivalent;
    protected $falseEquivalent;
    protected $parent;
    protected $info;
    protected $example;

    /**
     * Constructor
     *
     * @param string                $name   The name of the node
     * @param NodeParentInterface   $parent The parent
     */
    public function __construct($name, NodeParentInterface $parent = null)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->default = false;
        $this->required = false;
        $this->trueEquivalent = true;
        $this->falseEquivalent = false;
    }

    /**
     * Sets the parent node.
     *
     * @param NodeParentInterface $parent The parent
     */
    public function setParent(NodeParentInterface $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Sets info message.
     *
     * @param string $info The info text
     *
     * @return NodeDefinition
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Sets example configuration.
     *
     * @param string|array $example
     *
     * @return NodeDefinition
     */
    public function setExample($example)
    {
        $this->example = $example;

        return $this;
    }

    /**
     * Returns the parent node.
     *
     * @return NodeParentInterface The builder of the parent node
     */
    public function end()
    {
        return $this->parent;
    }

    /**
     * Creates the node.
     *
     * @param Boolean $forceRootNode Whether to force this node as the root node
     *
     * @return NodeInterface
     */
    public function getNode($forceRootNode = false)
    {
        if ($forceRootNode) {
            $this->parent = null;
        }

        if (null !== $this->normalization) {
            $this->normalization->before = ExprBuilder::buildExpressions($this->normalization->before);
        }

        if (null !== $this->validation) {
            $this->validation->rules = ExprBuilder::buildExpressions($this->validation->rules);
        }

        $node = $this->createNode();

        $node->setInfo($this->info);
        $node->setExample($this->example);

        return $node;
    }

    /**
     * Sets the default value.
     *
     * @param mixed $value The default value
     *
     * @return NodeDefinition
     */
    public function defaultValue($value)
    {
        $this->default = true;
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Sets the node as required.
     *
     * @return NodeDefinition
     */
    public function isRequired()
    {
        $this->required = true;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains null.
     *
     * @param mixed $value
     *
     * @return NodeDefinition
     */
    public function treatNullLike($value)
    {
        $this->nullEquivalent = $value;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains true.
     *
     * @param mixed $value
     *
     * @return NodeDefinition
     */
    public function treatTrueLike($value)
    {
        $this->trueEquivalent = $value;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains false.
     *
     * @param mixed $value
     *
     * @return NodeDefinition
     */
    public function treatFalseLike($value)
    {
        $this->falseEquivalent = $value;

        return $this;
    }

    /**
     * Sets null as the default value.
     *
     * @return NodeDefinition
     */
    public function defaultNull()
    {
        return $this->defaultValue(null);
    }

    /**
     * Sets true as the default value.
     *
     * @return NodeDefinition
     */
    public function defaultTrue()
    {
        return $this->defaultValue(true);
    }

    /**
     * Sets false as the default value.
     *
     * @return NodeDefinition
     */
    public function defaultFalse()
    {
        return $this->defaultValue(false);
    }

    /**
     * Sets an expression to run before the normalization.
     *
     * @return ExprBuilder
     */
    public function beforeNormalization()
    {
        return $this->normalization()->before();
    }

    /**
     * Denies the node value being empty.
     *
     * @return NodeDefinition
     */
    public function cannotBeEmpty()
    {
        $this->allowEmptyValue = false;

        return $this;
    }

    /**
     * Sets an expression to run for the validation.
     *
     * The expression receives the value of the node and must return it. It can
     * modify it.
     * An exception should be thrown when the node is not valid.
     *
     * @return ExprBuilder
     */
    public function validate()
    {
        return $this->validation()->rule();
    }

    /**
     * Sets whether the node can be overwritten.
     *
     * @param Boolean $deny Whether the overwriting is forbidden or not
     *
     * @return NodeDefinition
     */
    public function cannotBeOverwritten($deny = true)
    {
        $this->merge()->denyOverwrite($deny);

        return $this;
    }

    /**
     * Gets the builder for validation rules.
     *
     * @return ValidationBuilder
     */
    protected function validation()
    {
        if (null === $this->validation) {
            $this->validation = new ValidationBuilder($this);
        }

        return $this->validation;
    }

    /**
     * Gets the builder for merging rules.
     *
     * @return MergeBuilder
     */
    protected function merge()
    {
        if (null === $this->merge) {
            $this->merge = new MergeBuilder($this);
        }

        return $this->merge;
    }

    /**
     * Gets the builder for normalization rules.
     *
     * @return NormalizationBuilder
     */
    protected function normalization()
    {
        if (null === $this->normalization) {
            $this->normalization = new NormalizationBuilder($this);
        }

        return $this->normalization;
    }

    /**
     * Instantiate and configure the node according to this definition
     *
     * @return NodeInterface $node The node instance
     *
     * @throws Symfony\Component\Config\Definition\Exception\InvalidDefinitionException When the definition is invalid
     */
    abstract protected function createNode();

}
