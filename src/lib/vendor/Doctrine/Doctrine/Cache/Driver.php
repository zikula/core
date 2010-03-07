<?php
/*
 *  $Id: Driver.php 6821 2009-11-30 17:32:21Z jwage $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Abstract cache driver class
 *
 * @package     Doctrine
 * @subpackage  Cache
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 6821 $
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class Doctrine_Cache_Driver implements Doctrine_Cache_Interface
{
    /**
     * The key used to store the index of cache keys in this cache driver instance
     *
     * @var string
     */
    protected $_cacheKeyIndexKey = 'doctrine_cache_keys';

    /**
     * @var array $_options      an array of options
     */
    protected $_options = array();

    /**
     * Configure cache driver with an array of options
     *
     * @param array $_options      an array of options
     */
    public function __construct($options = array()) 
    {
        $this->_options = $options;
    }

    /**
     * Set option name and value
     *
     * @param mixed $option     the option name
     * @param mixed $value      option value
     * @return boolean          TRUE on success, FALSE on failure
     */
    public function setOption($option, $value)
    {
        if (isset($this->_options[$option])) {
            $this->_options[$option] = $value;
            return true;
        }
        return false;
    }

    /**
     * Get value of option
     * 
     * @param mixed $option     the option name
     * @return mixed            option value
     */
    public function getOption($option)
    {
        if ( ! isset($this->_options[$option])) {
            return null;
        }

        return $this->_options[$option];
    }

    /**
     * Get the number of cache records stored in this cache driver instance
     *
     * @return integer $count
     */
    public function count() 
    {
        $keys = $this->fetch($this->_cacheKeyIndexKey);
        return $keys ? count($keys) : 0;
    }

    /**
     * Fetch a cache record from this cache driver instance
     *
     * @param string $id cache id
     * @param boolean $testCacheValidity        if set to false, the cache validity won't be tested
     * @return mixed  Returns either the cached data or false
     */
    public function fetch($id, $testCacheValidity = true)
    {
        $key = $this->_getKey($id);
        return $this->_doFetch($key, $testCacheValidity);
    }

    /**
     * Test if a cache record exists for the passed id
     *
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function contains($id)
    {
        $key = $this->_getKey($id);
        return $this->_doContains($key);
    }

    /**
     * Save some string datas into a cache record
     *
     * @param string $id        cache id
     * @param string $data      data to cache
     * @param int $lifeTime     if != false, set a specific lifetime for this cache record (null => infinite lifeTime)
     * @param boolean $saveKey  Whether or not to save the key in the cache key index
     * @return boolean true if no problem
     */
    public function save($id, $data, $lifeTime = false, $saveKey = true)
    {
        $key = $this->_getKey($id);
        if ($this->_doSave($key, $data, $lifeTime)) {
            if ($saveKey) {
                $this->_saveKey($key);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove a cache record
     *
     * Note: This method accepts wildcards with the * character
     *
     * @param string $id cache id
     * @return boolean true if no problem
     */
    public function delete($id)
    {
        $key = $this->_getKey($id);

        if (strpos($key, '*') !== false) {
            return $this->deleteByRegex('/' . str_replace('*', '.*', $key) . '/');
        }

        if ($this->_doDelete($key)) {
            $this->_deleteKey($key);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete all cache records from the cache driver
     *
     * @return void
     */
    public function deleteAll()
    {
        $keys = $this->fetch($this->_cacheKeyIndexKey);
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    /**
     * Delete cache entries where the key matches a PHP regular expressions
     *
     * @param string $regex
     * @return integer $count The number of deleted cache entries
     */
    public function deleteByRegex($regex)
    {
        $count = 0;
        $keys = $this->fetch($this->_cacheKeyIndexKey);
        foreach ($keys as $key) {
            if (preg_match($regex, $key)) {
                $count++;
                $this->delete($key);
            }
        }
        return $count;
    }

    /**
     * Delete cache entries where the key has the passed prefix
     *
     * @param string $prefix
     * @return integer $count The number of deleted cache entries
     */
    public function deleteByPrefix($prefix)
    {
        $count = 0;
        $keys = $this->fetch($this->_cacheKeyIndexKey);
        foreach ($keys as $key) {
            if (strpos($key, $prefix) === 0) {
                $count++;
                $this->delete($key);
            }
        }
        return $count;
    }

    /**
     * Delete cache entries where the key has the passed suffix
     *
     * @param string $suffix 
     * @return integer $count The number of deleted cache entries
     */
    public function deleteBySuffix($suffix)
    {
        $count = 0;
        $keys = $this->fetch($this->_cacheKeyIndexKey);
        foreach ($keys as $key) {
            if (substr($key, -1 * strlen($suffix)) == $suffix) {
                $count++;
                $this->delete($key);
            }
        }
        return $count;
    }

    /**
     * Get the hash key passing its suffix
     *
     * @param string $id  The hash key suffix
     * @return string     Hash key to be used by drivers
     */
    protected function _getKey($id)
    {
        $prefix = isset($this->_options['prefix']) ? $this->_options['prefix'] : '';

        if ( ! $prefix || strpos($id, $prefix) === 0) {
            return $id;
        } else {
            return $prefix . $id;
        }
    }

    /**
     * Save a cache key in the index of cache keys
     *
     * @param string $key
     * @return boolean True if successful and false if something went wrong.
     */
    protected function _saveKey($key)
    {
        $keys = $this->fetch($this->_cacheKeyIndexKey);
        $keys[] = $key;

        return $this->save($this->_cacheKeyIndexKey, $keys, null, false);
    }

    /**
     * Delete a cache key from the index of cache keys
     *
     * @param string $key
     * @return boolean True if successful and false if something went wrong.
     */
    public function _deleteKey($key)
    {
        $keys = $this->fetch($this->_cacheKeyIndexKey);
        $key = array_search($key, $keys);
        if ($key !== false) {
            unset($keys[$key]);

            return $this->save($this->_cacheKeyIndexKey, $keys, null, false);
        }

        return false;
    }

    /**
     * Fetch a cache record from this cache driver instance
     *
     * @param string $id cache id
     * @param boolean $testCacheValidity        if set to false, the cache validity won't be tested
     * @return mixed  Returns either the cached data or false
     */
    abstract protected function _doFetch($id, $testCacheValidity = true);

    /**
     * Test if a cache record exists for the passed id
     *
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    abstract protected function _doContains($id);

    /**
     * Save a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::save()
     *
     * @param string $id        cache id
     * @param string $data      data to cache
     * @param int $lifeTime     if != false, set a specific lifetime for this cache record (null => infinite lifeTime)
     * @return boolean true if no problem
     */
    abstract protected function _doSave($id, $data, $lifeTime = false);

    /**
     * Remove a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::delete()
     * 
     * @param string $id cache id
     * @return boolean true if no problem
     */
    abstract protected function _doDelete($id);
}