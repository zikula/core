<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Controller;

use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\SecurityCenterModule\Entity\Repository\IntrusionRepository;
use Zikula\SecurityCenterModule\Form\Type\IdsLogExportType;
use Zikula\SecurityCenterModule\Form\Type\IdsLogFilterType;
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
     * @Template("@ZikulaSecurityCenterModule/IdsLog/view.html.twig")
     *
     * Function to view ids log events.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function viewAction(
        Request $request,
        IntrusionRepository $repository,
        RouterInterface $router
    ): array {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // sorting
        $sort = $request->query->get('sort', 'date DESC');
        $sort_exp = explode(' ', $sort);
        $sortField = $sort_exp[0];
        $sortDirection = $sort_exp[1] ?? 'ASC';
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
            'repository' => $repository
        ]);

        // number of items to show
        $pageSize = (int)$this->getVar('pagesize', 25);

        // offset
        $startOffset = (int)$request->query->getInt('startnum', 0);

        // get data
        $items = $repository->getIntrusions($where, $sorting, $pageSize, $startOffset);
        $amountOfItems = $repository->countIntrusions($where);

        $data = [];
        foreach ($items as $item) {
            $dta = $item->toArray();
            $dta['username'] = $dta['user']['uname'];
            $dta['filters'] = unserialize($dta['filters']);
            unset($dta['user']);
            $data[] = $dta;
        }

        $sortableColumns = new SortableColumns($router, 'zikulasecuritycentermodule_idslog_view', 'sort', 'sortdir');
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
        $sortableColumns->setOrderBy($sortableColumns->getColumn($sortField), mb_strtoupper($sortDirection));

        $templateParameters = [
            'filterForm' => $filterForm->createView(),
            'sort' => $sortableColumns,
            'logEntries' => $data,
            'pager' => [
                'amountOfItems' => $amountOfItems,
                'itemsPerPage' => $pageSize
            ]
        ];

        return $templateParameters;
    }

    /**
     * @Route("/export")
     * @Theme("admin")
     * @Template("@ZikulaSecurityCenterModule/IdsLog/export.html.twig")
     *
     * Export ids log.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|Response
     */
    public function exportAction(Request $request, IntrusionRepository $repository)
    {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(IdsLogExportType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('export')->isClicked()) {
                $formData = $form->getData();

                // export the titles ?
                $exportTitles = isset($formData['titles']) && 1 === $formData['titles'];

                // name of the exported file
                $exportFile = $formData['file'] ?? null;
                if (null === $exportFile || '' === $exportFile) {
                    $exportFile = 'idslog.csv';
                }
                if (!mb_strrpos($exportFile, '.csv')) {
                    $exportFile .= '.csv';
                }

                // delimeter
                $delimiter = $formData['delimiter'] ?? null;
                if (null === $delimiter || '' === $delimiter) {
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

                // get data
                $items = $repository->getIntrusions([], ['date' => 'DESC']);

                $string = $exportTitles ? implode($delimiter, $titles) . PHP_EOL : '';
                foreach ($items as $item) {
                    $dta = $item->toArray();
                    $dta['filters'] = unserialize($dta['filters']);
                    $filtersUsed = '';
                    foreach ($dta['filters'] as $filter) {
                        $filtersUsed .= $filter['id'] . ' ';
                    }
                    $string .=
                        $dta['name'] . $delimiter .
                        $dta['tag'] . $delimiter .
                        htmlspecialchars(str_replace(["\r\n", "\n"], ['', ''], $dta['value']), ENT_COMPAT, 'UTF-8', false) . $delimiter .
                        htmlspecialchars($dta['page'], ENT_COMPAT, 'UTF-8', false) . $delimiter .
                        $dta['user']['uname'] . $delimiter .
                        $dta['ip'] . $delimiter .
                        $dta['impact'] . $delimiter .
                        $filtersUsed . $delimiter .
                        $dta['date']->format('Y-m-d H:i:s') . PHP_EOL;
                }

                // create and export the csv file
                $response = new PlainResponse($string);
                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $exportFile
                );
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'text/csv');

                return $response;
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
     * @Template("@ZikulaSecurityCenterModule/IdsLog/purge.html.twig")
     *
     * Purge ids log.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function purgeAction(Request $request, IntrusionRepository $repository)
    {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(DeletionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                // delete all entries
                $repository->truncateTable();

                $this->addFlash('status', $this->__('Done! Purged IDS Log.'));
            } elseif ($form->get('cancel')->isClicked()) {
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
     * Delete an ids log entry.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws InvalidArgumentException Thrown if the object id is not numeric or if
     */
    public function deleteentryAction(Request $request, IntrusionRepository $repository): RedirectResponse
    {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete-idsentry', $request->query->get('token'))) {
            throw new AccessDeniedException();
        }

        // get parameters
        $id = (int)$request->query->get('id', 0);

        // sanity check
        if (!is_numeric($id)) {
            throw new InvalidArgumentException($this->__f("Error! Received a non-numeric object ID '%s'.", ['%s' => $id]));
        }

        $intrusion = $repository->find($id);
        if (!$intrusion) {
            $this->addFlash('error', $this->__f('Error! Invalid %s received.', ['%s' => "object ID [${id}]"]));
        } else {
            // delete object
            $this->getDoctrine()->getManager()->remove($intrusion);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('zikulasecuritycentermodule_idslog_view');
    }
}
