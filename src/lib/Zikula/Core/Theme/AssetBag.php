<?php

namespace Zikula\Core\Theme;

class AssetBag implements \IteratorAggregate, \Countable
{
    const DEFAULT_WEIGHT = 100;

    /**
     * array format:
     * $assets = [value => weight, value => weight, value => weight]
     * @var array
     */
    private $assets = array();

    public function __construct()
    {
    }

    /**
     * Add an array of assets to the Bag
     * A string value is allowed also
     *
     * @param string|array $asset
     */
    public function add($asset)
    {
        // ensure value is an array
        if (!is_array($asset)) {
            $asset = [$asset => self::DEFAULT_WEIGHT];
        }
        foreach ($asset as $source => $weight) {
            if ((isset($this->assets[$source]) && $this->assets[$source] > $weight) || !isset($this->assets[$source])) {
                // keep original weight if lighter. set if not set already.
                $this->assets[$source] = $weight;
            }
        }
        asort($this->assets); // put array in order by weight
    }

    public function remove($var)
    {
        unset($this->assets[$var]);
    }

    public function clear()
    {
        $this->assets = array();
    }

    public function all()
    {
        // returns sorted asset
        return array_keys($this->assets);
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->assets);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count()
    {
        return count($this->assets);
    }
}
