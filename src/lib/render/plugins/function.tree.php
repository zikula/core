<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to include the relevant files for the phpLayersMenu and pass a previously generated menu string to phpLayersMenu
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the results of the module function
 */
function smarty_function_tree ($params, &$smarty)
{
    $menuString = isset($params['menustring']) ? $params['menustring'] : null;
    $menuArray = isset($params['menuarray']) ? $params['menuarray'] : null;
    $config    = isset($params['config'])    ? $params['config']    : array();

    if(!isset($menuString) && !isset($menuArray)) {
        $smarty->trigger_error(__f('Error! in %1$s: %2$s or %3$s parameter must be specified.', array('smarty_function_tree', 'menustring', 'menuarray')));
        return false;
    }
    unset($params['menuString']);
    unset($params['menuArray']);
    unset($params['config']);
    $config = array_merge($config,(array)$params);

    $tree = new Tree($config);
    if(isset($menuArray)) {
        $tree->loadArrayData($menuArray);
    } else {
        $tree->loadStringData($menuString);
    }
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'],$tree->getHTML());
    } else {
        return $tree->getHTML();
    }
}

class Tree {
    private $config;
    private $data;
    private $tree;
    private $html;

    public function __construct($config=array())
    {
        $this->config = array(
            'sortable'      => false, // Tree lub TreeSortable
            'sortable'      => true, // Tree lub TreeSortable
            'cssFile'       => 'javascript/helpers/Tree/Tree.css',
            'imagesDir'     => 'javascript/helpers/Tree/',
            'plus'          => 'plus.gif',
            'minus'         => 'minus.gif',
            'parent'        => 'folder.gif',
            'parentOpen'    => 'folder_open.gif',
            'item'          => 'filenew.gif',
            'id'            => 'zikulatree',
            'wraperClass'   => 'treewraper',
            'class'         => 'tree',
            'nodePrefix'    => 'node_',
            'nullParent'    => 0
        );
        $this->setOptionArray($config);
    }
    public function setOption($key,$value)
    {
        $this->config[$key] = $value;
    }
    public function setOptionArray($array)
    {
        foreach((array)$array as $key => $value) {
            $this->setOption($key,$value);
        }
    }
    public function loadArrayData($menuArray)
    {
        $this->data = $menuArray;
        $this->parseData();
    }
    public function loadStringData($menuString)
    {
        $this->parseString($menuString);
        $this->parseData();
    }
    public function getHTML()
    {
        PageUtil::addVar('stylesheet', $this->config['cssFile']);
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'javascript/livepipe/livepipe.js');
        PageUtil::addVar('javascript', 'javascript/livepipe/cookie.js');
        PageUtil::addVar('javascript', 'javascript/helpers/Zikula.Tree.js');
        if($this->config['sortable']) {
            PageUtil::addVar('javascript', 'javascript/helpers/Zikula.TreeSortable.js');
        }
        $jsClass = $this->config['sortable'] ? 'Zikula.TreeSortable' : 'Zikula.Tree';
        $initScript = '
        <script type="text/javascript">
            document.observe("dom:loaded", function() {
                '.$jsClass.'.add("'.$this->config['id'].'");
            });
        </script>';
        PageUtil::addVar('rawtext', $initScript);
        $wraperClass = !empty($this->config['wraperClass']) ? 'class="'.$this->config['wraperClass'].'"' : '';
        $tree = $this->toHTML($this->tree,$this->config['id']);
        $this->html = "<div {$wraperClass}>{$tree}</div>";
        return $this->html;
    }
    private function parseString($menuString)
    {
        //level|text|href|title|icon|target|expanded
        $keys = array('level','name','href','title','icon','target','expanded');
        //id parent_id name title icon class active expanded href
        $lines = explode("\n",trim($menuString));
        $levels = array();
        foreach($lines as $id => $line) {
            $line = array_combine($keys,explode('|',trim($line)));
            $line['id'] = $id+1;
            $line['level'] = strlen($line['level']);
            $line['parent_id'] = isset($levels[$line['level']-1]) ? $levels[$line['level']-1] : $this->config['nullParent'];
            $levels[$line['level']] = $line['id'];
            $this->data[$line['id']] = $line;
        }
        return $this->data;
    }
    private function parseData()
    {
        $this->tree = array();
        $map = array();
        foreach($this->data as $item) {
            $item = array(
                'id' => isset($item['id']) ? $item['id'] : null,
                'parent_id' => isset($item['parent_id']) ? $item['parent_id'] : $this->config['nullParent'],
                'name' => isset($item['name']) ? $item['name'] : null,
                'title' => isset($item['title']) ? $item['title'] : null,
                'icon' => isset($item['icon']) ? $item['icon'] : null,
                'class' => isset($item['class']) ? $item['class'] : null,
                'active' => isset($item['active']) ? $item['active'] : true,
                'expanded' => isset($item['expanded']) ? $item['expanded'] : null,
                'href' => isset($item['href']) ? $item['href'] : '',
            );
            if(is_null($item['id'])) {
                continue;
            }
            $node = array('item' => $item, 'nodes' => array());
            if((int)$item['parent_id'] === $this->config['nullParent']) {
                $this->tree[$item['id']] = $node;
                $path = null;
            } else {
                $path = $map[$item['parent_id']];
                $path[] = $item['parent_id'];
                $handle =& $this->tree;
                while (list($key, $value) = each($path)) {
                    if($value === $this->config['nullParent']) continue;
                    $handle =& $handle[$value]['nodes'];
                }
                $handle[$item['id']] = $node;
            }
            $map[$item['id']] = $path;
        }
        return $this->tree;
    }
    private function toHTML($tree,$treeId=null)
    {
        $liHtml = array();
        $size = count($tree);
        $i = 1;
        foreach ($tree as $id => $tab) {
            $links = array();
            $item = $tab['item'];
            $toggle = '<img class="toggle" src="'.$this->config['imagesDir'].$this->config['minus'].'">';

            $iconImage = !empty($item['icon']) ? $item['icon'] : $this->config['item'];
            $iconImage = !empty($tab['nodes']) ?  $this->config['parentOpen'] : $this->config['item'];
            $icon = '<img class="icon" src="'.$this->config['imagesDir'].$iconImage.'">';

            $class = $item['active'] == 1 ? $item['class'] : 'unactive '.$item['class'];
            $linkClass = !empty($class) ? ' class="'.$class.'"' : '';
            $linkHref = 'href="'.$item['href'].'"';
            $linkTitle = !empty($item['title']) ? ' title="'.$item['title'].'"' : '';

            $links[] = "<a {$linkHref} {$linkTitle} {$linkClass}>{$item['name']}</a>";

            $subhtml = !empty($tab['nodes']) ? $this->toHTML($tab['nodes']) : '';

            $liId = !empty($this->config['nodePrefix']) ? ' id="'.$this->config['nodePrefix'].$id.'"' : '';
            $links = implode('',$links);
            $liClass = array();
            $liClass[] = $size == 1 ? 'single' : '';
            $liClass[] = ($i == 1 && $size > 1) ? 'first' : '';
            $liClass[] = ($i == $size && $size > 1) ? 'last' : '';
            $liClass[] = !empty($tab['nodes']) ? 'parent' : 'leaf';
            $liClass = trim(implode(' ', $liClass));
            $i++;
            $liClass ='class="'.$liClass.'"';
            $liHtml[] = "<li {$liId} {$liClass}>{$toggle}{$icon}{$links}{$subhtml}</li>";
        }

        $ulID = !empty($treeId) ? ' id="'.$treeId.'"' : '';
        $ulClass = !empty($this->config['class']) ? ' class="'.$this->config['class'].'"' : '';
        $ulClass = ' class="tree"';
        $liHtml = implode('',$liHtml);
        $html = "<ul {$ulID} {$ulClass}>{$liHtml}</ul>";

        return $html;
    }
}