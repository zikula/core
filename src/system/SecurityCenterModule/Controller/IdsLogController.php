<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Controller;

use FileUtil;
use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\SecurityCenterModule\Form\Type\IdsLogExportType;
use Zikula\SecurityCenterModule\Form\Type\IdsLogFilterType;
use Zikula\SecurityCenterModule\Form\Type\IdsLogPurgeType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class IdsLogController
 * @Route("/idslog")
 */
class IdsLogController extends AbstractController
{
    /**
     * @Route("/view")
     * @Theme("admin")
     * @Template
     *
     * Function to view ids log events.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function viewAction(Request $request)
    {
        // Security check
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // sorting
        $sort = $request->query->get('sort', 'date DESC');
        $sort_exp = explode(' ', $sort);
        $sortField = $sort_exp[0];
        $sortDirection = isset($sort_exp[1]) ? $sort_exp[1] : 'ASC';
        $sorting = [$sortField => $sortDirection];

        // filtering
        $defaultFilter = [
            'uid' => 0,
            'name' => null,
            'tag' => null,
            'value' => null,
            'page' => null,
            'ip' => null,
            'impact' => null
        ];
        $filters = $request->query->get('filter', $defaultFilter);
        $where = [];
        foreach ($filters as $flt_key => $flt_value) {
            if (isset($flt_value) && !empty($flt_value)) {
                $where[$flt_key] = $flt_value;
            }
        }

        $filterForm = $this->createForm(IdsLogFilterType::class, $filters, [
            'translator' => $this->get('translator.default'),
            'repository' => $this->get('zikula_securitycenter_module.intrusion_repository')
        ]);

        // offset
        $startOffset = $request->query->getDigits('startnum', 0);

        // number of items to show
        $pageSize = (int)$this->getVar('pagesize', 25);

        // get data
        $queryParameters = [
            'where' => $where,
            'sorting' => $sorting,
            'limit' => $pageSize,
            'offset' => $startOffset
        ];
        $items = ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'getAllIntrusions', $queryParameters);
        $amountOfItems = ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'countAllIntrusions', $queryParameters);

        $data = [];
        foreach ($items as $item) {
            $dta = $item->toArray();
            $dta['username'] = $dta['user']['uname'];
            $dta['filters'] = unserialize($dta['filters']);
            unset($dta['user']);
            $data[] = $dta;
        }

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulasecuritycentermodule_idslog_view', 'sort', 'sortdir');
        $sortableColumns->addColumns([
            new Column('name'),
            new Column('tag'),
            new Column('value'),
            new Column('page'),
            new Column('username'),
            new Column('ip'),
            new Column('impact'),
            new Column('date')
        ]);
        $sortableColumns->setOrderBy($sortableColumns->getColumn($sortField), strtoupper($sortDirection));

        $tokenHandler = $this->get('zikula_core.common.csrf_token_handler');

        $templateParameters = [
            'filterForm' => $filterForm->createView(),
            'sort' => $sortableColumns,
            'logEntries' => $data,
            'pager' => [
                'amountOfItems' => $amountOfItems,
                'itemsPerPage' => $pageSize
            ],
            'csrftoken' => $tokenHandler->generate(true)
        ];

        return $templateParameters;
    }

    /**
     * @Route("/export")
     * @Theme("admin")
     * @Template
     *
     * Export ids log.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function exportAction(Request $request)
    {
        // Security check
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(IdsLogExportType::class, [], [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('export')->isClicked()) {
                $formData = $form->getData();

                // export the titles ?
                $exportTitles = isset($formData['titles']) && $formData['titles'] == 1 ? true : false;

                // name of the exported file
                $exportFile = isset($formData['file']) ? $formData['file'] : null;
                if (is_null($exportFile) || $exportFile == '') {
                    $exportFile = 'idslog.csv';
                }
                if (!strrpos($exportFile, '.csv')) {
                    $exportFile .= '.csv';
                }

                // delimeter
                $delimiter = isset($formData['delimiter']) ? $formData['delimiter'] : null;
                if (is_null($delimiter) || $delimiter == '') {
                    $delimiter = 1;
                }
                switch ($delimiter) {
                    case 1:
                        $delimiter = ',';
                        break;
                    case 2:
                        $delimiter = ';';
                        break;
                    case 3:
                        $delimiter = ':';
                        break;
                    case 4:
                        $delimiter = chr(9);
                }

                // titles
                $titles = [];
                if ($exportTitles === true) {
                    $titles = [
                        $this->__('Name'),
                        $this->__('Tag'),
                        $this->__('Value'),
                        $this->__('Page'),
                        $this->__('User Name'),
                        $this->__('IP'),
                        $this->__('Impact'),
                        $this->__('PHPIDS filters used'),
                        $this->__('Date')
                    ];
                }

                // get data
                $itemParams = [
                    'sorting' => ['date' => 'DESC']
                ];
                $items = ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'getAllIntrusions', $itemParams);

                $objData = [];
                foreach ($items as $item) {
                    $dta = $item->toArray();
                    $dta['username'] = $dta['user']['uname'];
                    $dta['filters'] = unserialize($dta['filters']);
                    $dta['date'] = $dta['date']->format('Y-m-d H:i:s');
                    unset($dta['user']);
                    $objData[] = $dta;
                }

                $data = [];
                $find = ["\r\n", "\n"];
                $replace = ['', ''];

                foreach ($objData as $key => $idsdata) {
                    $filtersUsed = '';
                    foreach ($objData[$key]['filters'] as $filter) {
                        $filtersUsed .= $filter['id'] . ' ';
                    }

                    $dataRow = [
                        $objData[$key]['name'],
                        $objData[$key]['tag'],
                        htmlspecialchars(str_replace($find, $replace, $objData[$key]['value']), ENT_COMPAT, 'UTF-8', false),
                        htmlspecialchars($objData[$key]['page'], ENT_COMPAT, 'UTF-8', false),
                        $objData[$key]['username'],
                        $objData[$key]['ip'],
                        $objData[$key]['impact'],
                        $filtersUsed,
                        $objData[$key]['date']
                    ];

                    array_push($data, $dataRow);
                }

                // export the csv file
                FileUtil::exportCSV($data, $titles, $delimiter, '"', $exportFile);
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));

                return $this->redirectToRoute('zikulasecuritycentermodule_idslog_view');
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/purge")
     * @Theme("admin")
     * @Template
     *
     * Purge ids log.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function purgeAction(Request $request)
    {
        // Security check
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(IdsLogPurgeType::class, [], [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $formData = $form->getData();

                if ($formData['confirmation'] == 1) {
                    // delete all entries
                    if (ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'purgeidslog')) {
                        $this->addFlash('status', $this->__('Done! Purged IDS Log.'));
                    }
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulasecuritycentermodule_idslog_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/deleteentry")
     *
     * Delete an ids log entry
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if the object id is not numeric or if
     */
    public function deleteentryAction(Request $request)
    {
        // verify auth-key
        $csrftoken = $request->get('csrftoken');
        $tokenHandler = $this->get('zikula_core.common.csrf_token_handler');
        $tokenHandler->validate($csrftoken);

        // Security check
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // get parameters
        $id = (int)$request->get('id', 0);

        // sanity check
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException($this->__f("Error! Received a non-numeric object ID '%s'.", ['%s' => $id]));
        }

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $intrusion = $entityManager->find('ZikulaSecurityCenterModule:IntrusionEntity', $id);

        // check for valid object
        if (!$intrusion) {
            $this->addFlash('error', $this->__f('Error! Invalid %s received.', ['%s' => "object ID [$id]"]));
        } else {
            // delete object
            $entityManager->remove($intrusion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('zikulasecuritycentermodule_idslog_view');
    }
}
