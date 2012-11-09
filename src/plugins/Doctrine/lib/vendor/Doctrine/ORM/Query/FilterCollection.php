<?php
/*
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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Query;

use Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager;

/**
 * Collection class for all the query filters.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class FilterCollection
{
    /* Filter STATES */
    /**
     * A filter object is in CLEAN state when it has no changed parameters.
     */
    const FILTERS_STATE_CLEAN  = 1;

    /**
     * A filter object is in DIRTY state when it has changed parameters.
     */
    const FILTERS_STATE_DIRTY = 2;

    /**
     * The used Configuration.
     *
     * @var Doctrine\ORM\Configuration
     */
    private $config;

    /**
     * The EntityManager that "owns" this FilterCollection instance.
     *
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Instances of enabled filters.
     *
     * @var array
     */
    private $enabledFilters = array();

    /**
     * @var string The filter hash from the last time the query was parsed.
     */
    private $filterHash;

    /**
     * @var integer $state The current state of this filter
     */
    private $filtersState = self::FILTERS_STATE_CLEAN;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->config = $em->getConfiguration();
    }

    /**
     * Get all the enabled filters.
     *
     * @return array The enabled filters.
     */
    public function getEnabledFilters()
    {
        return $this->enabledFilters;
    }

    /**
     * Enables a filter from the collection.
     *
     * @param string $name Name of the filter.
     *
     * @throws \InvalidArgumentException If the filter does not exist.
     *
     * @return SQLFilter The enabled filter.
     */
    public function enable($name)
    {
        if (null === $filterClass = $this->config->getFilterClassName($name)) {
            throw new \InvalidArgumentException("Filter '" . $name . "' does not exist.");
        }

        if (!isset($this->enabledFilters[$name])) {
            $this->enabledFilters[$name] = new $filterClass($this->em);

            // Keep the enabled filters sorted for the hash
            ksort($this->enabledFilters);

            // Now the filter collection is dirty
            $this->filtersState = self::FILTERS_STATE_DIRTY;
        }

        return $this->enabledFilters[$name];
    }

    /**
     * Disables a filter.
     *
     * @param string $name Name of the filter.
     *
     * @return SQLFilter The disabled filter.
     *
     * @throws \InvalidArgumentException If the filter does not exist.
     */
    public function disable($name)
    {
        // Get the filter to return it
        $filter = $this->getFilter($name);

        unset($this->enabledFilters[$name]);

        // Now the filter collection is dirty
        $this->filtersState = self::FILTERS_STATE_DIRTY;

        return $filter;
    }

    /**
     * Get an enabled filter from the collection.
     *
     * @param string $name Name of the filter.
     *
     * @return SQLFilter The filter.
     *
     * @throws \InvalidArgumentException If the filter is not enabled.
     */
    public function getFilter($name)
    {
        if (!isset($this->enabledFilters[$name])) {
            throw new \InvalidArgumentException("Filter '" . $name . "' is not enabled.");
        }

        return $this->enabledFilters[$name];
    }

    /**
     * @return boolean True, if the filter collection is clean.
     */
    public function isClean()
    {
        return self::FILTERS_STATE_CLEAN === $this->filtersState;
    }

    /**
     * Generates a string of currently enabled filters to use for the cache id.
     *
     * @return string
     */
    public function getHash()
    {
        // If there are only clean filters, the previous hash can be returned
        if (self::FILTERS_STATE_CLEAN === $this->filtersState) {
            return $this->filterHash;
        }

        $filterHash = '';
        foreach ($this->enabledFilters as $name => $filter) {
            $filterHash .= $name . $filter;
        }

        return $filterHash;
    }

    /**
     * Set the filter state to dirty.
     */
    public function setFiltersStateDirty()
    {
        $this->filtersState = self::FILTERS_STATE_DIRTY;
    }
}
