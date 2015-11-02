<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Form\Handler\Route\Base;

use Zikula\RoutesModule\Form\Handler\Common\EditHandler as BaseEditHandler;


use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FormUtil;
use ModUtil;
use SecurityUtil;
use System;
use UserUtil;
use Zikula_Form_View;

/**
 * This handler class handles the page events of editing forms.
 * It aims on the route object type.
 *
 * More documentation is provided in the parent class.
 */
class EditHandler extends BaseEditHandler
{
    /**
     * Pre-initialise hook.
     *
     * @return void
     */
    public function preInitialize()
    {
        parent::preInitialize();
    
        $this->objectType = 'route';
        $this->objectTypeCapital = 'Route';
        $this->objectTypeLower = 'route';
    
        $this->hasPageLockSupport = true;
        // array with list fields and multiple flags
        $this->listFields = array('workflowState' => false, 'routeType' => false, 'schemes' => true, 'methods' => true);
    }

    /**
     * Initialize form handler.
     *
     * This method takes care of all necessary initialisation of our data and form states.
     *
     * @param Zikula_Form_View $view The form view instance.
     *
     * @return boolean False in case of initialization errors, otherwise true.
     */
    public function initialize(Zikula_Form_View $view)
    {
        $result = parent::initialize($view);
        if ($result === false) {
            return $result;
        }
    
        if ($this->mode == 'create') {
            $modelHelper = $this->view->getServiceManager()->get('zikularoutesmodule.model_helper');
            if (!$modelHelper->canBeCreated($this->objectType)) {
                $logger = $this->view->getServiceManager()->get('logger');
                $logger->notice('{app}: User {user} tried to create a new {entity}, but failed as it other items are required which must be created before.', array('app' => 'ZikulaRoutesModule', 'user' => UserUtil::getVar('uname'), 'entity' => $this->objectType));
    
                return $this->view->redirect($this->getRedirectUrl(null));
            }
        }
    
        $entity = $this->entityRef;
    
        // save entity reference for later reuse
        $this->entityRef = $entity;
    
        $entityData = $entity->toArray();
    
        if (count($this->listFields) > 0) {
            $helper = $this->view->getServiceManager()->get('zikularoutesmodule.listentries_helper');
    
            foreach ($this->listFields as $listField => $isMultiple) {
                $entityData[$listField . 'Items'] = $helper->getEntries($this->objectType, $listField);
                if ($isMultiple) {
                    $entityData[$listField] = $helper->extractMultiList($entityData[$listField]);
                }
            }
        }
    
        // assign data to template as array (makes translatable support easier)
        $this->view->assign($this->objectTypeLower, $entityData);
    
        if ($this->mode == 'edit') {
            // assign formatted title
            $this->view->assign('formattedEntityTitle', $entity->getTitleFromDisplayPattern());
        }
    
        // everything okay, no initialization errors occured
        return true;
    }

    /**
     * Post-initialise hook.
     *
     * @return void
     */
    public function postInitialize()
    {
        parent::postInitialize();
    }

    /**
     * Get list of allowed redirect codes.
     *
     * @return array list of possible redirect codes
     */
    protected function getRedirectCodes()
    {
        $codes = parent::getRedirectCodes();
    
        return $codes;
    }

    /**
     * Get the default redirect url. Required if no returnTo parameter has been supplied.
     * This method is called in handleCommand so we know which command has been performed.
     *
     * @param array  $args List of arguments.
     *
     * @return string The default redirect url.
     */
    protected function getDefaultReturnUrl($args)
    {
        $serviceManager = $this->view->getServiceManager();
    
        $legacyControllerType = $this->request->query->filter('lct', 'user', false, FILTER_SANITIZE_STRING);
    
        // redirect to the list of routes
        $viewArgs = array('lct' => $legacyControllerType);
        $url = $serviceManager->get('router')->generate('zikularoutesmodule_' . strtolower($this->objectType) . '_view', $viewArgs);
    
        return $url;
    }

    /**
     * Command event handler.
     *
     * This event handler is called when a command is issued by the user.
     *
     * @param Zikula_Form_View $view The form view instance.
     * @param array            $args Additional arguments.
     *
     * @return mixed Redirect or false on errors.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        $result = parent::handleCommand($view, $args);
        if ($result === false) {
            return $result;
        }
    
        return $this->view->redirect($this->getRedirectUrl($args));
    }
    
    /**
     * Get success or error message for default operations.
     *
     * @param Array   $args    Arguments from handleCommand method.
     * @param Boolean $success Becomes true if this is a success, false for default error.
     *
     * @return String desired status or error message.
     */
    protected function getDefaultMessage($args, $success = false)
    {
        if ($success !== true) {
            return parent::getDefaultMessage($args, $success);
        }
    
        $message = '';
        switch ($args['commandName']) {
            case 'submit':
                        if ($this->mode == 'create') {
                            $message = $this->__('Done! Route created.');
                        } else {
                            $message = $this->__('Done! Route updated.');
                        }
                        break;
            case 'delete':
                        $message = $this->__('Done! Route deleted.');
                        break;
            default:
                        $message = $this->__('Done! Route updated.');
                        break;
        }
    
        return $message;
    }

    /**
     * This method executes a certain workflow action.
     *
     * @param Array $args Arguments from handleCommand method.
     *
     * @return bool Whether everything worked well or not.
     *
     * @throws RuntimeException Thrown if concurrent editing is recognised or another error occurs
     */
    public function applyAction(array $args = array())
    {
        // get treated entity reference from persisted member var
        $entity = $this->entityRef;
    
        $action = $args['commandName'];
    
        $success = false;
        try {
            // execute the workflow action
            $workflowHelper = $this->view->getServiceManager()->get('zikularoutesmodule.workflow_helper');
            $success = $workflowHelper->executeAction($entity, $action);
        } catch(\Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__f('Sorry, but an unknown error occured during the %s action. Please apply the changes again!', array($action)));
            $logger = $this->view->getServiceManager()->get('logger');
            $logger->error('{app}: User {user} tried to edit the {entity} with id {id}, but failed. Error details: {errorMessage}.', array('app' => 'ZikulaRoutesModule', 'user' => UserUtil::getVar('uname'), 'entity' => 'route', 'id' => $entity->createCompositeIdentifier(), 'errorMessage' => $e->getMessage()));
        }
    
        $this->addDefaultMessage($args, $success);
    
        if ($success && $this->mode == 'create') {
            // store new identifier
            foreach ($this->idFields as $idField) {
                $this->idValues[$idField] = $entity[$idField];
            }
        }
    
    
        return $success;
    }

    /**
     * Get url to redirect to.
     *
     * @param array  $args List of arguments.
     *
     * @return string The redirect url.
     */
    protected function getRedirectUrl($args)
    {
        $serviceManager = $this->view->getContainer();
    
        if ($this->inlineUsage == true) {
            $urlArgs = array('idPrefix'    => $this->idPrefix,
                             'commandName' => $args['commandName']);
            foreach ($this->idFields as $idField) {
                $urlArgs[$idField] = $this->idValues[$idField];
            }
    
            // inline usage, return to special function for closing the Zikula.UI.Window instance
            return $serviceManager->get('router')->generate('zikularoutesmodule_' . strtolower($this->objectType) . '_handleinlineredirect', $urlArgs);
        }
    
        if ($this->repeatCreateAction) {
            return $this->repeatReturnUrl;
        }
    
        // normal usage, compute return url from given redirect code
        if (!in_array($this->returnTo, $this->getRedirectCodes())) {
            // invalid return code, so return the default url
            return $this->getDefaultReturnUrl($args);
        }
    
        // parse given redirect code and return corresponding url
        switch ($this->returnTo) {
            case 'admin':
                return $serviceManager->get('router')->generate('zikularoutesmodule_' . strtolower($this->objectType) . '_index', array('lct' => 'admin'));
            case 'adminView':
                return $serviceManager->get('router')->generate('zikularoutesmodule_' . strtolower($this->objectType) . '_view', array('lct' => 'admin'));
            case 'adminDisplay':
                if ($args['commandName'] != 'delete' && !($this->mode == 'create' && $args['commandName'] == 'cancel')) {
                    foreach ($this->idFields as $idField) {
                        $urlArgs[$idField] = $this->idValues[$idField];
                    }
                    $urlArgs['lct'] = 'admin';
                    return $serviceManager->get('router')->generate('zikularoutesmodule_' . strtolower($this->objectType) . '_display', $urlArgs);
                }
                return $this->getDefaultReturnUrl($args);
            default:
                return $this->getDefaultReturnUrl($args);
        }
    }
}
