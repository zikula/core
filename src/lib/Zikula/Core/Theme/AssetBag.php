<?php

namespace Zikula\Core\Theme;

class AssetBag implements \IteratorAggregate, \Countable
{
    private $assets;

    public function __construct(array $assets = array())
    {
        $this->assets = $assets;
    }

//    public function set($asset)
//    {
//        $this->assets[] = $asset;
//        $this->assets = array_unique($this->assets);
//    }

    public function add($asset)
    {
        $this->assets[] = $asset;
        $this->assets = array_unique($this->assets);
    }

    public function remove($var)
    {
        if ($key = array_search($var, $this->assets)) {
            unset($this->assets[$key]);
        }
    }

    public function clear()
    {
        $this->assets = array();
    }

    public function all()
    {
        return $this->assets;
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
