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
        $serviceManager = $this->eventManager->getServiceManager();
        $logType = isset($serviceManager['log.to_debug_toolbar_output']) ? $serviceManager['log.to_debug_toolbar_output'] : 0;

        switch ($logType) {
            case 0:
                // normal toolbar
                return $this->asHTML();
            case 1:
                // only data in json format
                return $this->asJSON();
            case 2:
                // toolbar and data in json format
                return $this->asHTML() . $this->asJSON();
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
        $serviceManager = $this->eventManager->getServiceManager();
        $request = $serviceManager->getService('request');

        // check if security key is defined
        $secKey = isset($serviceManager['log.to_debug_toolbar_seckey']) ? $serviceManager['log.to_debug_toolbar_seckey'] : false;
        // if so - get client seckey from http header
        if (!empty($secKey)) {
            $requestSecKey =$request->getServer()->get('HTTP_X_ZIKULA_DEBUGTOOLBAR');
            // if client seckey is not valid - do not return data
            if ($secKey != $requestSecKey) {
                return '';
            }
        }

        $data = array();

        $data['__meta'] = array(
            'realpath' => realpath('.')
        );

        $data['http_request'] = array(
            'method' => $request->getMethod(),
            'get' => (array)$request->query->getCollection(),
            'post' => (array)$request->request->getCollection(),
            'files' => (array)$request->files->getCollection(),
            'cookie' => (array)$request->getCookie()->getCollection(),
            'server' => (array)$request->server->getCollection(),
            'env' => (array)$request->getEnv()->getCollection(),
        );

        foreach ($this->_panels as $name => $panel) {
            $title = $panel->getPanelTitle();

            $data[$name] = array(
                'title' => $title ? $title : $name,
                'content' => $panel->getPaneldata()
            );
        }
        // need to suppress errors due to recursion warrnings
        $data = @json_encode($data);

        $html = "<script type=\"text/javascript\">/* <![CDATA[ */ \nZikula.DebugToolbarData = {$data}\n /* ]]> */</script>";

        return $html;
    }

    /**
     * Parse data and prepare objects for json encode.
     *
     * This method loops through data and prepares php objects for json encode.
     * First each object is converted to array with additional entry:
     * '__phpClassName', which contains object name.
     * Next, depending on maxLvl param, it reads objects properties and saves them
     * in array.
     *
     * @param mixed   $data   Data to parse.
     * @param integer $maxLvl Maximum data deepth for objects (default 0).
     * @param integer $lvl    Current level, for internal use in recursive loops.
     *
     * @return mixed processed data
     */
    public static function prepareData($data, $maxLvl = 0, $lvl = 0)
    {
        $return = array();
        if (is_object($data) || is_array($data)) {
            if ($lvl > $maxLvl && is_object($data)) {
                $return = array(
                    '__phpClassName' => get_class($data)
                );
            } elseif (is_object($data)) {
                $obj = array();
                $class = get_class($data);
                $obj['__phpClassName'] = $class;

                $reflectionClass = new ReflectionClass($class);
                $properties = array();
                foreach ($reflectionClass->getProperties() as $property) {
                    $properties[$property->getName()] = $property;
                }

                $members = (array)$data;

                foreach ($properties as $raw_name => $property) {
                    $name = $raw_name;
                    if ($property->isStatic()) {
                        $name = 'static:'.$name;
                    }
                    if ($property->isPublic()) {
                        $name = 'public:'.$name;
                    } elseif ($property->isPrivate()) {
                        $name = 'private:'.$name;
                        $raw_name = "\0".$class."\0".$raw_name;
                    } elseif ($property->isProtected()) {
                        $name = 'protected:'.$name;
                        $raw_name = "\0".'*'."\0".$raw_name;
                    }
                    if (array_key_exists($raw_name, $members) && !$property->isStatic()) {
                        $obj[$name] = self::prepareData($members[$raw_name], $maxLvl, $lvl + 1);
                    } else {
                        if (method_exists($property, 'setAccessible')) {
                            $property->setAccessible(true);
                            $obj[$name] = self::prepareData($property->getValue($data), $maxLvl, $lvl + 1);
                        } elseif ($property->isPublic()) {
                            $obj[$name] = self::prepareData($property->getValue($data), $maxLvl, $lvl + 1);
                        }
                    }
                }

                $return = $obj;

            } elseif (is_array($data)) {
                foreach ($data as $k => $v) {
                    $return[$k] = self::prepareData($v, $maxLvl, $lvl);
                }
            }
        } else {
            $return = $data;
        }

        return $return;
    }


}
