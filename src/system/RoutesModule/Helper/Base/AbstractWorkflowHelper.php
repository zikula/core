<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 1.1.0 (https://modulestudio.de).
 */

namespace Zikula\RoutesModule\Helper\Base;

use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Registry;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\RoutesModule\Entity\Factory\EntityFactory;
use Zikula\RoutesModule\Helper\ListEntriesHelper;

/**
 * Helper base class for workflow methods.
 */
abstract class AbstractWorkflowHelper
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PermissionApiInterface
     */
    protected $permissionApi;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var ListEntriesHelper
     */
    protected $listEntriesHelper;

    /**
     * WorkflowHelper constructor.
     *
     * @param TranslatorInterface     $translator        Translator service instance
     * @param Registry                $registry          Workflow registry service instance
     * @param LoggerInterface         $logger            Logger service instance
     * @param PermissionApiInterface  $permissionApi     PermissionApi service instance
     * @param CurrentUserApiInterface $currentUserApi    CurrentUserApi service instance
     * @param EntityFactory           $entityFactory     EntityFactory service instance
     * @param ListEntriesHelper       $listEntriesHelper ListEntriesHelper service instance
     *
     * @return void
     */
    public function __construct(
        TranslatorInterface $translator,
        Registry $registry,
        LoggerInterface $logger,
        PermissionApiInterface $permissionApi,
        CurrentUserApiInterface $currentUserApi,
        EntityFactory $entityFactory,
        ListEntriesHelper $listEntriesHelper
    ) {
        $this->translator = $translator;
        $this->workflowRegistry = $registry;
        $this->logger = $logger;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->entityFactory = $entityFactory;
        $this->listEntriesHelper = $listEntriesHelper;
    }

    /**
      * This method returns a list of possible object states.
      *
      * @return array List of collected state information
      */
     public function getObjectStates()
     {
         $states = [];
         $states[] = [
             'value' => 'initial',
             'text' => $this->translator->__('Initial'),
             'ui' => 'danger'
         ];
         $states[] = [
             'value' => 'approved',
             'text' => $this->translator->__('Approved'),
             'ui' => 'success'
         ];
         $states[] = [
             'value' => 'deleted',
             'text' => $this->translator->__('Deleted'),
             'ui' => 'danger'
         ];
    
         return $states;
     }
    
    /**
     * This method returns information about a certain state.
     *
     * @param string $state The given state value
     *
     * @return array|null The corresponding state information
     */
    public function getStateInfo($state = 'initial')
    {
        $result = null;
        $stateList = $this->getObjectStates();
        foreach ($stateList as $singleState) {
            if ($singleState['value'] != $state) {
                continue;
            }
            $result = $singleState;
            break;
        }
    
        return $result;
    }
    
    /**
     * Retrieve the available actions for a given entity object.
     *
     * @param EntityAccess $entity The given entity instance
     *
     * @return array List of available workflow actions
     */
    public function getActionsForObject(EntityAccess $entity)
    {
        $workflow = $this->workflowRegistry->get($entity);
        $wfActions = $workflow->getEnabledTransitions($entity);
        $currentState = $entity->getWorkflowState();
    
        $actions = [];
        foreach ($wfActions as $action) {
            $actionId = $action->getName();
            $actions[$actionId] = [
                'id' => $actionId,
                'title' => $this->getTitleForAction($currentState, $actionId),
                'buttonClass' => $this->getButtonClassForAction($actionId)
            ];
        }
    
        return $actions;
    }
    
    /**
     * Returns a translatable title for a certain action.
     *
     * @param string $currentState Current state of the entity
     * @param string $actionId     Id of the treated action
     *
     * @return string The action title
     */
    protected function getTitleForAction($currentState, $actionId)
    {
        $title = '';
        switch ($actionId) {
            case 'submit':
                $title = $this->translator->__('Submit');
                break;
            case 'delete':
                $title = $this->translator->__('Delete');
                break;
        }
    
        if ($title == '' && substr($actionId, 0, 6) == 'update') {
            $title = $this->translator->__('Update');
        }
    
        return $title;
    }
    
    /**
     * Returns a button class for a certain action.
     *
     * @param string $actionId Id of the treated action
     *
     * @return string The button class
     */
    protected function getButtonClassForAction($actionId)
    {
        $buttonClass = '';
        switch ($actionId) {
            case 'submit':
                $buttonClass = 'success';
                break;
            case 'delete':
                $buttonClass = 'danger';
                break;
        }
    
        if ($buttonClass == '' && substr($actionId, 0, 6) == 'update') {
            $buttonClass = 'success';
    	}
    
        if (empty($buttonClass)) {
            $buttonClass = 'default';
        }
    
        return 'btn btn-' . $buttonClass;
    }
    
    /**
     * Executes a certain workflow action for a given entity object.
     *
     * @param EntityAccess $entity    The given entity instance
     * @param string       $actionId  Name of action to be executed
     * @param bool         $recursive True if the function called itself
     *
     * @return bool False on error or true if everything worked well
     */
    public function executeAction(EntityAccess $entity, $actionId = '', $recursive = false)
    {
        $workflow = $this->workflowRegistry->get($entity);
        if (!$workflow->can($entity, $actionId)) {
            return false;
        }
    
        // get entity manager
        $entityManager = $this->entityFactory->getObjectManager();
        $logArgs = ['app' => 'ZikulaRoutesModule', 'user' => $this->currentUserApi->get('uname')];
    
        $result = false;
    
        try {
            $workflow->apply($entity, $actionId);
    
            if ($actionId == 'delete') {
                $entityManager->remove($entity);
            } else {
                $entityManager->persist($entity);
            }
            $entityManager->flush();
    
            $result = true;
            if ($actionId == 'delete') {
                $this->logger->notice('{app}: User {user} deleted an entity.', $logArgs);
            } else {
                $this->logger->notice('{app}: User {user} updated an entity.', $logArgs);
            }
        } catch (\Exception $exception) {
            if ($actionId == 'delete') {
                $this->logger->error('{app}: User {user} tried to delete an entity, but failed.', $logArgs);
            } else {
                $this->logger->error('{app}: User {user} tried to update an entity, but failed.', $logArgs);
            }
            throw new \RuntimeException($exception->getMessage());
        }
    
        if (false !== $result && !$recursive) {
            $entities = $entity->getRelatedObjectsToPersist();
            foreach ($entities as $rel) {
                if ($rel->getWorkflowState() == 'initial') {
                    $this->executeAction($rel, $actionId, true);
                }
            }
        }
    
        return (false !== $result);
    }
    
}
