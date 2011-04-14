<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
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
    protected $config;

    /**
     * Data.
     *
     * @var array
     */
    protected $data;

    /**
     * Tree data.
     *
     * @var array
     */
    protected $tree;

    /**
     * HTML output.
     *
     * @var string
     */
    protected $html;

    /**
     * Constructor.
     *
     * @param array $config Config array.
     */
    public function __construct(array $config = array())
    {
        $this->config = array(
            'sortable'      => false,
            'withWraper'    => true,
            'cssFile'       => 'javascript/helpers/Tree/Tree.css',
            'imagesDir'     => 'javascript/helpers/Tree/',
            'plus'          => 'plus.gif',
            'minus'         => 'minus.gif',
            'parent'        => 'folder.png',
            'parentOpen'    => 'folder_open.png',
            'item'          => 'filenew.png',
            'id'            => 'zikulatree',
            'wraperClass'   => 'treewraper',
            'treeClass'     => 'tree',
            'nodePrefix'    => 'node_',
            'nullParent'    => 0, // what value is used for root level nodes? for example 0, null, '' (empty string)
            'toggler'       => 'z-tree-toggle',
            'icon'          => 'z-tree-icon',
            'nodeUnactive'  => 'z-tree-unactive',
            'nodeSingle'    => 'z-tree-single',
            'nodeFirst'     => 'z-tree-first',
            'nodeLast'      => 'z-tree-last',
            'nodeParent'    => 'z-tree-parent',
            'nodeLeaf'      => 'z-tree-leaf'
        );
        $this->setOptionArray($config);
    }

    /**
     * Set option.
     *
     * @param string $key   Key.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function setOption($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * Set option array.
     *
     * @param array $array Options.
     *
     * @return void
     */
    public function setOptionArray($array)
    {
        foreach ((array)$array as $key => $value) {
            $this->setOption($key,$value);
        }
    }

    /**
     * Load array data.
     *
     * @param array $menuArray Array data.
     *
     * @return void
     */
    public function loadArrayData($menuArray)
    {
        $this->data = $menuArray;
        $this->_parseData();
    }

    /**
     * Load string data.
     *
     * @param string $menuString Data string.
     *
     * @return void
     */
    public function loadStringData($menuString)
    {
        $this->_parseString($menuString);
        $this->_parseData();
    }

    /**
     * Set tree data. Given array must be already proper structured tree data.
     *
     * @param array $treeArray Data array.
     *
     * @return void
     */
    public function setTreeData($treeArray)
    {
        $this->tree = (array)$treeArray;
    }

    /**
     * Get HTML output
     *
     * @return string HTML output.
     */
    public function getHTML()
    {
        PageUtil::addVar('stylesheet', $this->config['cssFile']);
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'livepipe');
        PageUtil::addVar('javascript', 'javascript/helpers/Zikula.Tree.js');
        $jsClass = $this->config['sortable'] ? 'Zikula.TreeSortable' : 'Zikula.Tree';
        $initScript = "
        <script type=\"text/javascript\">
            document.observe('dom:loaded', function() {
                {$jsClass}.add('{$this->config['id']}','{$this->getConfigForScript()}');
            });
        </script>";
        PageUtil::addVar('header', $initScript);
        $tree = $this->_toHTML($this->tree,$this->config['id'],true);
        if ($this->config['withWraper']) {
            $wraperClass = !empty($this->config['wraperClass']) ? 'class="'.$this->config['wraperClass'].'"' : '';
            $this->html = "<div {$wraperClass}>{$tree}</div>";
        } else {
            $this->html = $tree;
        }
        return $this->html;
    }

    /**
     * Get configuration for script.
     *
     * @param boolean $encode Whether or not to json encode the configuration.
     *
     * @return string|array
     */
    public function getConfigForScript($encode = true)
    {
        $jsConfig = $this->config;
        $imagesKeys = array('plus','minus','parent','parentOpen','item');
        $jsConfig['images'] = array();
        foreach ($imagesKeys as $img) {
            $jsConfig['images'][$img] = $this->config[$img];
            unset($jsConfig[$img]);
        }
        return $encode ? json_encode($jsConfig) : $jsConfig;
    }

    /**
     * Parse string.
     *
     * @param string $menuString Data string.
     *
     * @return array Data array.
     */
    protected function _parseString($menuString)
    {
        //level|text|href|title|icon|target|expanded
        $keys = array('level','name','href','title','icon','target','expanded');
        //id parent_id name title icon class active expanded href
        $lines = explode("\n",trim($menuString));
        $levels = array();
        foreach ($lines as $id => $line) {
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
     * Parse data
     *
     * @return array Tree data.
     */
    protected function _parseData()
    {
        $this->tree = array();
        $map = array();
        foreach ($this->data as $item) {
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
            if (is_null($item['id'])) {
                continue;
            }
            $node = array('item' => $item, 'nodes' => array());
            if ((string)$item['parent_id'] == (string)$this->config['nullParent']) {
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
     * Convert tree data to HTML.
     *
     * @param array   $tree   Tree data.
     * @param string  $treeId Tree Id.
     * @param boolean $root   Is this root level.
     *
     * @return string HTML output.
     */
    protected function _toHTML($tree, $treeId = null, $root = false)
    {
        $liHtml = array();
        $size = count($tree);
        $i = 1;
        foreach ($tree as $id => $tab) {
            $subhtml = !empty($tab['nodes']) ? $this->_toHTML($tab['nodes']) : '';
            $liHtml[] = $this->_nodeToHTML($id, $tab, $size, $i, $subhtml);
            $i++;
        }

        $liHtml = implode('',$liHtml);
        if ($root && !$this->config['withWraper']) {
            $html = $liHtml;
        } else {
            $ulID = !empty($treeId) ? ' id="'.$treeId.'"' : '';
            $ulClass = !empty($this->config['treeClass']) ? ' class="'.$this->config['treeClass'].'"' : '';
            $html = "<ul {$ulID} {$ulClass}>{$liHtml}</ul>";
        }
        return $html;
    }


    /**
     * Parse single tree node to HTML
     *
     * @param int    $id      Node id.
     * @param array  $tab     Node data.
     * @param int    $size    Tree size.
     * @param int    $i       Current node index.
     * @param string $nodeSub HTML code for subnodes if node has such, default null.
     *
     * @return string Node HTML code
     */
    protected function _nodeToHTML($id, $tab, $size, $i, $nodeSub = null)
    {
        $links = array();
        $item = $tab['item'];
        $toggle = '<img class="'.$this->config['toggler'].'" alt="" src="'.$this->config['imagesDir'].$this->config['minus'].'" />';

        $iconImage = !empty($item['icon']) ? $item['icon'] : $this->config['item'];
        $iconImage = !empty($tab['nodes']) ?  $this->config['parentOpen'] : $this->config['item'];
        $icon = '<img class="'.$this->config['icon'].'" alt="" src="'.$this->config['imagesDir'].$iconImage.'" />';

        $class = $item['active'] == 1 ? $item['class'] : $this->config['nodeUnactive'].' '.$item['class'];
        $linkClass = !empty($class) ? ' class="'.$class.'"' : '';
        $linkHref = 'href="'.DataUtil::formatForDisplay($item['href']).'"';
        $linkTitle = !empty($item['title']) ? ' title="'.$item['title'].'"' : '';

        $links[] = "<a {$linkHref} {$linkTitle} {$linkClass}>{$item['name']}</a>";

        $liId = !empty($this->config['nodePrefix']) ? ' id="'.$this->config['nodePrefix'].$id.'"' : '';
        $links = implode('',$links);
        $liClass = array();
        $liClass[] = $size == 1 ? $this->config['nodeSingle'] : '';
        $liClass[] = ($i == 1 && $size > 1) ? $this->config['nodeFirst'] : '';
        $liClass[] = ($i == $size && $size > 1) ? $this->config['nodeLast'] : '';
        $liClass[] = !empty($tab['nodes']) ? $this->config['nodeParent'] : $this->config['nodeLeaf'];
        $liClass = trim(implode(' ', $liClass));
        $liClass ='class="'.$liClass.'"';

        return "<li {$liId} {$liClass}>{$toggle}{$icon}{$links}{$nodeSub}</li>";
    }
}
