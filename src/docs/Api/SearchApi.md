SearchApi
=========

classname: \Zikula\SearchModule\Api\SearchApi

service id="zikula_search_module.api.search_api"

This class is used to perform a global search.

The class makes the following methods available:

    /**
     * Perform the search.
     *
     * @param string $q             query string to search
     * @param bool   $firstPage     (optional) is this first search attempt? is so - basic search is performed
     * @param string $searchType    (optional) search type (default='AND')
     * @param string $searchOrder   (optional) search order (default='newest')
     * @param int    $limit         (optional) number of items to return (default value based on Search settings, -1 for no limit)
     * @param int    $page          (optional) page number (default=1)
     * @param array  $moduleData    (optional) array of module-provided data from Zikula\SearchModule\SearchableInterface::amendForm
     *
     * @return array array of items array and result count
     */
    public function search($q, $firstPage = false, $searchType = 'AND', $searchOrder = 'newest', $limit = -1, $page = 1, array $moduleData = []);

    /**
     * Log search query for search statistics.
     *
     * @param string|null $q
     */
    public function log($q = null);
