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
        ZLoader::addAutoloader("Symfony\Component\Form", __DIR__ . '/lib/vendor', '\\');
        ZLoader::addAutoloader("Symfony\Component\EventDispatcher", __DIR__ . '/lib/vendor', '\\');
        
        
        $csrf = new \Symfony\Component\Form\Extension\Csrf\CsrfExtension(new Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider("asd654aASadsDF5d5asd"));
        $core = new \Symfony\Component\Form\Extension\Core\CoreExtension();
        
        $formFactory = new \Symfony\Component\Form\FormFactory(array($core, $csrf));
        
        $this->serviceManager->attachService('symfony.formfactory', $formFactory);
        
        
        $formRenderer = new SystemPlugin_Symfony2Forms_FormRenderer($this->eventManager);
        $this->serviceManager->attachService('symfony.formrenderer', $formRenderer);
    }
    
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('view.init', 'initView');
        $this->addHandlerDefinition('symfony.formrenderer.lookup', 'registerRenderer');
    }
    
    public function initView(Zikula_Event $event) 
    {
        /* @var $view Zikula_View */
        $view = $event->getSubject();
        
        $view->register_function('sform_enctype', array($this->serviceManager->getService('symfony.formrenderer'), 
                                                    'renderEnctype'));
        
        $view->register_function('sform_row', array($this->serviceManager->getService('symfony.formrenderer'), 
                                                    'renderRow'));
        
        $view->register_function('sform_label', array($this->serviceManager->getService('symfony.formrenderer'), 
                                                    'renderLabel'));
        
        $view->register_function('sform_errors', array($this->serviceManager->getService('symfony.formrenderer'), 
                                                    'renderErrors'));
        
        $view->register_function('sform_widget', array($this->serviceManager->getService('symfony.formrenderer'), 
                                                    'renderWidget'));
        
        $view->register_function('sform_rest', array($this->serviceManager->getService('symfony.formrenderer'), 
                                                    'renderRest'));
    }
    
    public function registerRenderer(Zikula_Event $event)
    {
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FieldRow());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FieldLabel());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FieldErrors());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_EmailWidget());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FieldWidget());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_Attributes());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FieldEnctype());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FormWidget());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_ContainerAttributes());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FieldRows());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_FieldRest());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_HiddenRow());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_HiddenWidget());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_CheckboxWidget());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_ChoiceOptions());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_ChoiceWidget());
        $event->getSubject()->append(new SystemPlugin_Symfony2Forms_Renderer_DateWidget());
    }
}
