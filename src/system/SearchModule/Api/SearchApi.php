<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Api;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SearchModule\Api\ApiInterface\SearchApiInterface;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchResultRepositoryInterface;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;

class SearchApi implements SearchApiInterface
{
    /**
     * @var VariableApi
     */
    protected $variableApi;

    /**
     * @var SearchResultRepositoryInterface
     */
    protected $searchResultRepository;

    /**
     * @var SearchStatRepositoryInterface
     */
    protected $searchStatRepository;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var SearchableModuleCollector
     */
    protected $searchableModuleCollector;

    /**
     * SearchApi constructor.
     * @param VariableApi $variableApi
     * @param SearchResultRepositoryInterface $searchResultRepository
     * @param SearchStatRepositoryInterface $searchStatRepository
     * @param SessionInterface $session
     * @param SearchableModuleCollector $searchableModuleCollector
     */
    public function __construct(
        VariableApi $variableApi,
        SearchResultRepositoryInterface $searchResultRepository,
        SearchStatRepositoryInterface $searchStatRepository,
        SessionInterface $session,
        SearchableModuleCollector $searchableModuleCollector
    ) {
        $this->variableApi = $variableApi;
        $this->searchResultRepository = $searchResultRepository;
        $this->searchStatRepository = $searchStatRepository;
        $this->session = $session;
        $this->searchableModuleCollector = $searchableModuleCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function search($q, $firstPage = false, $searchType = 'AND', $searchOrder = 'newest', $limit = -1, $page = 1, array $moduleData = [])
    {
        $limit = isset($limit) && !empty($limit) ? $limit : $this->variableApi->get('ZikulaSearchModule', 'itemsperpage', 25);
        $offset = $limit > 0 ? (($page - 1) * $limit) : 0;

        // obtain and persist search results from searchableModules
        if ($firstPage) {
            // Clear current search result for current user - before showing the first page
            // Clear also older searches from other users.
            $this->searchResultRepository->clearOldResults($this->session->getId());
            // convert query string to an *array* of words
            $words = ($searchType == 'EXACT') ? [trim($q)] : preg_split('/ /', $q, -1, PREG_SPLIT_NO_EMPTY);
            $searchableModules = $this->searchableModuleCollector->getAll();
            foreach ($searchableModules as $moduleName => $searchableInstance) {
                if (isset($moduleData['active']) && !$moduleData['active']) {
                    continue;
                }
                $moduleFormData = isset($moduleData[$moduleName]) ? $moduleData[$moduleName] : null;
                $results = $searchableInstance->getResults($words, $searchType, $moduleFormData);
                foreach ($results as $searchResult) {
                    $this->searchResultRepository->persist($searchResult);
                }
            }
            $this->searchResultRepository->flush();

            $resultCount = $this->searchResultRepository->countResults($this->session->getId());
            $this->session->set('searchResultCount', $resultCount);
        } else {
            $resultCount = $this->session->get('searchResultCount');
        }

        $results = $this->searchResultRepository->getResults(['sesid' => $this->session->getId()], $this->computeSort($searchOrder), $limit, $offset);

        $result = [
            'count' => $resultCount,
            'results' => $results,
        ];
        if (isset($searchableInstance)) {
            $result['errors'] = $searchableInstance->getErrors();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function log($q = null)
    {
        if (!isset($q)) {
            return;
        }

        $obj = $this->searchStatRepository->findOneBy(['search' => $q]);
        if (!$obj) {
            $obj = new SearchStatEntity();
        }
        $obj->incrementCount();
        $obj->setSearch($q);
        $obj->setDate(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->searchStatRepository->persistAndFlush($obj);
    }

    private function computeSort($searchOrder)
    {
        switch ($searchOrder) {
            case 'oldest':
                return ['created' => 'ASC'];
            case 'newest':
                return ['created' => 'DESC'];
            case 'alphabetical':
            default:
                return ['title' => 'ASC'];
        }
    }
}
