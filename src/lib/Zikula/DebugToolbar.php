<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_DebugToolbar
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * An small toolbar to help debugging zikula.
 *
 * A Toolbar contains a set of panels. All panels are accessible with its own name.
 * The constructor sends an event with the name 'debugtoolbar.init' with an instance of this class as subject.
 * You can listen to this event and extend the toolbar with custom panels.
 *
 * Example:
 * <code>
 * class MyPanel implements Zikula_DebugToolbar_PanelInterface
 * {
 *     public function getId()
 *     {
 *         return "mypan";
 *     }
 * 
 *     public function getTitle()
 *     {
 *         return "MyPan';
 *     }
 *
 *     public function getPanelTitle()
 *     {
 *         return 'Title of the content panel';
 *     }
 *
 *     public function getPanelContent()
 *     {
 *         return 'HTML-Code of the content panel here';
 *     }
 *
 *     public function getPanelData()
 *     {
 *         return 'Plain panel data here';
 *     }
 * }
 * </code>
 */
class Zikula_DebugToolbar
{
    /**
     * Array of all panels. The key contains the name of the panel.
     *
     * @var array
     */
    private $_panels = array();

    /**
     * Event Manager instance.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * Sends an event via the EventManager to allow other code to extend the toolbar.
     *
     * @param Zikula_EventManager $eventManager Core event manager.
     */
    public function __construct(Zikula_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'javascript/debugtoolbar/main.js');
        PageUtil::addVar('stylesheet', 'style/debugtoolbar.css');

        // allow modules and plugins to extend the toolbar
        $event = new Zikula_Event('debugtoolbar.init', $this);
        $this->eventManager->notify($event);
    }

    /**
     * Adds a panel to the panel list.
     *
     * An panel with an already used id will be overwritten
     *
     * @param Zikula_DebugToolbar_PanelInterface $panel Panel object.
     *
     * @return void
     * @throws InvalidArgumentException When $panel is null.
     */
    public function addPanel(Zikula_DebugToolbar_PanelInterface $panel)
    {
        if ($panel == null) {
            throw new InvalidArgumentException(__f('Error! in %1$s: invalid value for the %2$s parameter (%3$s).', array('Zikula_DebugToolbar::addPanel', 'panel', 'null')));
        }

        $this->_panels[$panel->getId()] = $panel;
    }

    /**
     * Add multiple panels to the panel list.
     *
     * @return void
     */
    public function addPanels()
    {
        foreach (func_get_args() as $panel) {
            $this->addPanel($panel);
        }
    }

    /**
     * Returns the HTML code for this debug toolbar.
     * 
     * @return string
     */
    public function getContent()
    {
        // check which output type should be returned
        $sm = $this->eventManager->getServiceManager();
        $logtype = isset($sm['log.to_debug_toolbar_output']) ? $sm['log.to_debug_toolbar_output'] : 0;

        switch ($logtype) {
            case 0:
                // normal toolbar
                return $this->asHTML();
            case 1:
                // only data in json format
                return $this->asJSON();
            case 2:
                // toolbar and data in json format
                return $this->asHTML().$this->asJSON();
        }
    }
    /**
     * Returns the HTML code for this debug toolbar.
     * 
     * @return string
     */
    public function asHTML()
    {
        $links         = array();
        $panelContents = array();

        // generate HTML Code for all panels
        foreach ($this->_panels as $name => $panel) {
            $title = $panel->getTitle();

            // ignore panels without a title
            if ($title) {
                $content = $panel->getPanelContent();

                if ($content) {
                    // show title with a link to the content
                    $id = 'DebugToolbarPanel'.$name.'Content';
                    $links[]         = '<li title="'.$panel->getPanelTitle().'" class="'.$name.'"><a href="#" onclick="defaultZikulaDebugToolbar.toggleContentForPanel(\''.$id.'\');return false;">'.$title.'</a></li>';
                    $panelContents[] = '<div id="'.$id.'" class="DebugToolbarPanel" style="display:none;"><h1>'.$panel->getPanelTitle().'</h1>'.$panel->getPanelContent().'</div>';
                } else {
                    // show title without a link
                    $links[] = '<li title="'.$panel->getPanelTitle().'" class="'.$name.'">'.$title.'</li>';
                }
            }
        }

        // generate final html code
        return '<div id="DebugToolbarContainer">
                    <div id="DebugToolbar">
                        <a href="#" onclick="defaultZikulaDebugToolbar.toggleBar(); return false;"><img src="'.System::getBaseUri().'/images/logo_small.png" alt="Debug toolbar" /></a>

                        <ul id="DebugToolbarLinks">
                            '.implode(' ', $links).'
                            <li class="last">
                                <a href="#" onclick="$(\'DebugToolbarContainer\').hide(); return false;">X</a>
                            </li>
                        </ul>
                    </div>

                    '.implode(' ', $panelContents).'
                </div>';
    }

    /**
     * Returns the toolbar data in json format
     * 
     * @return string
     */
    public function asJSON()
    {
        $data = array();
        foreach ($this->_panels as $name => $panel) {
            $title = $panel->getPanelTitle();

            $data[$name] = array(
                'title' => $title ? $title : $name,
                'content' => $panel->getPaneldata()
            );
        }
        // need to suppress errors due to recursion warrnings
        $data = @json_encode($data);

        $html = "<script type=\"text/javascript\">Zikula.DebugToolbarData = {$data}</script>";
        return $html;
    }
}
