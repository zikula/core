<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Tree class.
 */
class Zikula_Tree
{
    /**
     * Configuration.
     * 
     * @var array
     */
    private $config;

    /**
     *
     * @var <type>
     */
    private $data;

    /**
     *
     * @var <type>
     */
    private $tree;

    /**
     *
     * @var <type> 
     */
    private $html;

    /**
     * Constructor.
     *
     * @param array $config Config array.
     */
    public function __construct(array $config=array())
    {
        $this->config = array(
            'sortable'      => false,
            'cssFile'       => 'javascript/helpers/Tree/Tree.css',
            'imagesDir'     => 'javascript/helpers/Tree/',
            'plus'          => 'plus.gif',
            'minus'         => 'minus.gif',
            'parent'        => 'folder.gif',
            'parentOpen'    => 'folder_open.gif',
            'item'          => 'filenew.gif',
            'id'            => 'zikulatree',
            'wraperClass'   => 'treewraper',
            'treeClass'     => 'tree',
            'nodePrefix'    => 'node_',
            'nullParent'    => 0,
            'toggler'       => 'toggle',
            'icon'          => 'icon',
            'nodeUnactive'  => 'unactive',
            'nodeSingle'    => 'single',
            'nodeFirst'     => 'first',
            'nodeLast'      => 'last',
            'nodeParent'    => 'parent',
            'nodeLeaf'      => 'leaf'
        );
        $this->setOptionArray($config);
    }

    /**
     *
     * @param <type> $key
     * @param <type> $value
     */
    public function setOption($key,$value)
    {
        $this->config[$key] = $value;
    }

    /**
     *
     * @param <type> $array
     */
    public function setOptionArray($array)
    {
        foreach((array)$array as $key => $value) {
            $this->setOption($key,$value);
        }
    }

    /**
     *
     * @param <type> $menuArray
     */
    public function loadArrayData($menuArray)
    {
        $this->data = $menuArray;
        $this->_parseData();
    }

    /**
     *
     * @param <type> $menuString
     */
    public function loadStringData($menuString)
    {
        $this->_parseString($menuString);
        $this->_parseData();
    }

    /**
     *
     * @return <type>
     */
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
        $initScript = "
        <script type=\"text/javascript\">
            document.observe('dom:loaded', function() {
                {$jsClass}.add('{$this->config['id']}','{$this->getConfigForScript()}');
            });
        </script>";
        PageUtil::addVar('rawtext', $initScript);
        $wraperClass = !empty($this->config['wraperClass']) ? 'class="'.$this->config['wraperClass'].'"' : '';
        $tree = $this->_toHTML($this->tree,$this->config['id']);
        $this->html = "<div {$wraperClass}>{$tree}</div>";
        return $this->html;
    }

    /**
     *
     * @param <type> $encode
     * @return <type>
     */
    public function getConfigForScript($encode=true)
    {
        $jsConfig = $this->config;
        $imagesKeys = array('plus','minus','parent','parentOpen','item');
        $jsConfig['images'] = array();
        foreach($imagesKeys as $img) {
            $jsConfig['images'][$img] = $this->config[$img];
            unset($jsConfig[$img]);
        }
        return $encode ? json_encode($jsConfig) : $jsConfig;
    }

    /**
     *
     * @param <type> $menuString
     * @return <type>
     */
    private function _parseString($menuString)
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

    /**
     *
     * @return <type>
     */
    private function _parseData()
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

    /**
     *
     * @param <type> $tree
     * @param <type> $treeId
     * @return <type> 
     */
    private function _toHTML($tree,$treeId=null)
    {
        $liHtml = array();
        $size = count($tree);
        $i = 1;
        foreach ($tree as $id => $tab) {
            $links = array();
            $item = $tab['item'];
            $toggle = '<img class="'.$this->config['toggler'].'" alt="" src="'.$this->config['imagesDir'].$this->config['minus'].'" />';

            $iconImage = !empty($item['icon']) ? $item['icon'] : $this->config['item'];
            $iconImage = !empty($tab['nodes']) ?  $this->config['parentOpen'] : $this->config['item'];
            $icon = '<img class="'.$this->config['icon'].'" alt="" src="'.$this->config['imagesDir'].$iconImage.'" />';

            $class = $item['active'] == 1 ? $item['class'] : $this->config['nodeUnactive'].' '.$item['class'];
            $linkClass = !empty($class) ? ' class="'.$class.'"' : '';
            $linkHref = 'href="'.$item['href'].'"';
            $linkTitle = !empty($item['title']) ? ' title="'.$item['title'].'"' : '';

            $links[] = "<a {$linkHref} {$linkTitle} {$linkClass}>{$item['name']}</a>";

            $subhtml = !empty($tab['nodes']) ? $this->_toHTML($tab['nodes']) : '';

            $liId = !empty($this->config['nodePrefix']) ? ' id="'.$this->config['nodePrefix'].$id.'"' : '';
            $links = implode('',$links);
            $liClass = array();
            $liClass[] = $size == 1 ? $this->config['nodeSingle'] : '';
            $liClass[] = ($i == 1 && $size > 1) ? $this->config['nodeFirst'] : '';
            $liClass[] = ($i == $size && $size > 1) ? $this->config['nodeLast'] : '';
            $liClass[] = !empty($tab['nodes']) ? $this->config['nodeParent'] : $this->config['nodeLeaf'];
            $liClass = trim(implode(' ', $liClass));
            $i++;
            $liClass ='class="'.$liClass.'"';
            $liHtml[] = "<li {$liId} {$liClass}>{$toggle}{$icon}{$links}{$subhtml}</li>";
        }

        $ulID = !empty($treeId) ? ' id="'.$treeId.'"' : '';
        $ulClass = !empty($this->config['treeClass']) ? ' class="'.$this->config['treeClass'].'"' : '';
        $liHtml = implode('',$liHtml);
        $html = "<ul {$ulID} {$ulClass}>{$liHtml}</ul>";

        return $html;
    }
}
