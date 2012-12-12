<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula\Core\Forms\Renderer;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Symfony2 forms plugin definition.
 */
class SystemPlugin_Symfony2Forms_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_AlwaysOnInterface
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Symfony2 Forms'),
                     'description' => $this->__('Provides Form Component of Symfony2'),
                     'version'     => '1.0.0'
                      );
    }
    
    public function initialize()
    {
        // class loading
        ZLoader::addAutoloader("Zikula\\Core\\Forms", __DIR__ . '/lib', '\\');

        // register symfony validation annorations
        Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('Symfony\\Component\\Validator\\Constraints', __DIR__ . '/../../vendor/symfony/validator');

        // register validator service
        $fileLocator = new FileLocator(array(__DIR__ . '/Resources/config/validator.xml'));
        $xmlFileLoader = new XmlFileLoader($this->serviceManager->get('service_container'), $fileLocator);
        $xmlFileLoader->load(__DIR__ . '/Resources/config/validator.xml');

        // setup symfony forms
        $registry = new \Zikula\Core\Forms\DoctrineRegistryImpl();
        $csrf = new \Symfony\Component\Form\Extension\Csrf\CsrfExtension(new \Zikula\Core\Forms\ZikulaCsrfProvider());
        $core = new \Symfony\Component\Form\Extension\Core\CoreExtension();
        $validator = new \Symfony\Component\Form\Extension\Validator\ValidatorExtension($this->serviceManager->get("validator"));
        $zk = new \Zikula\Core\Forms\ZikulaExtension();
        $doctrine = new \Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($registry);
        $formFactory = new \Symfony\Component\Form\FormFactory(array($core, $csrf, $validator, $zk, $doctrine));

        $this->serviceManager->set('symfony.formfactory', $formFactory);
        
        
        $formRenderer = new \Zikula\Core\Forms\FormRenderer($this->eventManager);
        $this->serviceManager->set('symfony.formrenderer', $formRenderer);
    }
    
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('view.init', 'initView');
        $this->addHandlerDefinition('symfony.formrenderer.lookup', 'registerRenderer');
    }
    
    public function initView(GenericEvent $event) 
    {
        /* @var $view Zikula_View */
        $view = $event->getSubject();
        
        $view->register_function('sform_enctype', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderEnctype'));
        
        $view->register_function('sform_row', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderRow'));
        
        $view->register_function('sform_label', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderLabel'));
        
        $view->register_function('sform_errors', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderErrors'));
        
        $view->register_function('sform_widget', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderWidget'));
        
        $view->register_function('sform_rest', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderRest'));
        
        $view->register_function('sform_all_errors', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderGlobalErrors'));
        
        $view->register_block('sform', array($this->serviceManager->get('symfony.formrenderer'),
                                                    'renderFormTag'));
    }
    
    public function registerRenderer(Zikula_Event $event)
    {
        $event->getSubject()->append(new Renderer\FieldRow());
        $event->getSubject()->append(new Renderer\FieldLabel());
        $event->getSubject()->append(new Renderer\FieldErrors());
        $event->getSubject()->append(new Renderer\EmailWidget());
        $event->getSubject()->append(new Renderer\FieldWidget());
        $event->getSubject()->append(new Renderer\Attributes());
        $event->getSubject()->append(new Renderer\FieldEnctype());
        $event->getSubject()->append(new Renderer\FormWidget());
        $event->getSubject()->append(new Renderer\ContainerAttributes());
        $event->getSubject()->append(new Renderer\FieldRows());
        $event->getSubject()->append(new Renderer\FieldRest());
        $event->getSubject()->append(new Renderer\HiddenRow());
        $event->getSubject()->append(new Renderer\HiddenWidget());
        $event->getSubject()->append(new Renderer\CheckboxWidget());
        $event->getSubject()->append(new Renderer\ChoiceOptions());
        $event->getSubject()->append(new Renderer\ChoiceWidget());
        $event->getSubject()->append(new Renderer\DateWidget());
        $event->getSubject()->append(new Renderer\DatetimeWidget());
        $event->getSubject()->append(new Renderer\FormLabel());
        $event->getSubject()->append(new Renderer\IntegerWidget());
        $event->getSubject()->append(new Renderer\MoneyWidget());
        $event->getSubject()->append(new Renderer\NumberWidget());
        $event->getSubject()->append(new Renderer\PasswordWidget());
        $event->getSubject()->append(new Renderer\PercentWidget());
        $event->getSubject()->append(new Renderer\PrototypeRow());
        $event->getSubject()->append(new Renderer\RadioWidget());
        $event->getSubject()->append(new Renderer\RepeatedRow());
        $event->getSubject()->append(new Renderer\SearchWidget());
        $event->getSubject()->append(new Renderer\TextareaWidget());
        $event->getSubject()->append(new Renderer\TimeWidget());
        $event->getSubject()->append(new Renderer\UrlWidget());
        $event->getSubject()->append(new Renderer\FormErrors());
    }
}
