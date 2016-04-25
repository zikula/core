<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_Tree class.
 *
 * @deprecated
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
            'objid'         => 'id',
            'customJSClass' => '',
            'nestedSet'     => false,
            'renderRoot'    => true,
            'id'            => 'zikulatree',
            'treeClass'     => 'tree',
            'nodePrefix'    => 'node_',
            'nullParent'    => 0, // what value is used for root level nodes? for example 0, null, '' (empty string)
            'sortable'      => false,
            'withWraper'    => true,
            'wraperClass'   => 'treewraper',
            'cssFile'       => System::getBaseUri() . '/javascript/helpers/Tree/Tree.css',
            'item'          => 'filenew.png',
            'toggler'       => 'z-tree-toggle',
            'icon'          => 'z-tree-icon',
            'nodeUnactive'  => 'z-tree-unactive',
            'nodeSingle'    => 'z-tree-single',
            'nodeFirst'     => 'z-tree-first',
            'nodeLast'      => 'z-tree-last',
            'nodeParent'    => 'z-tree-parent',
            'nodeLeaf'      => 'z-tree-leaf',
            'fixedParent'   => 'z-tree-fixedparent',
            'imagesDir'     => System::getBaseUri() . '/javascript/helpers/Tree/',
            'plus'          => 'plus.gif',
            'minus'         => 'minus.gif',
            'parent'        => 'folder.png',
            'parentOpen'    => 'folder_open.png'
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
            $this->setOption($key, $value);
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
        if ($this->config['customJSClass']) {
            $jsClass = $this->config['customJSClass'];
        } else {
            $jsClass = $this->config['sortable'] ? 'Zikula.TreeSortable' : 'Zikula.Tree';
        }
        $initScript = "
        <script type=\"text/javascript\">
            document.observe('dom:loaded', function() {
                {$jsClass}.add('{$this->config['id']}', '{$this->getConfigForScript()}');
            });
        </script>";
        PageUtil::addVar('header', $initScript);

        $tree = $this->_toHTML($this->tree, 1, $this->config['id'], true);

        if ($this->config['withWraper']) {
            $wraperClass = !empty($this->config['wraperClass']) ? 'class="'.$this->config['wraperClass'].'"' : '';
            $this->html = "\n<div {$wraperClass}>\n{$tree}</div>";
        } else {
            $this->html = $tree;
        }

        return $this->html;
    }

    /**
     * Get HTML output using jQuery
     *
     * @return string HTML output.
     */
    public function getJqueryHtml()
    {
        PageUtil::addVar('javascript', 'jquery');
        PageUtil::addVar('javascript', 'web/jstree/dist/jstree.min.js');
        PageUtil::addVar('stylesheet', 'web/jstree/dist/themes/default/style.min.css');

        $tree = $this->_toHTML($this->tree, 1, $this->config['id'], true);

        if ($this->config['withWraper']) {
            $wraperClass = !empty($this->config['wraperClass']) ? 'class="'.$this->config['wraperClass'].'"' : '';
            $this->html = "\n<div {$wraperClass}>\n{$tree}</div>";
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
        $omitKeys = array('objid', 'nestedSet', 'customJSClass'. 'cssFile');
        foreach ($omitKeys as $key) {
            unset($jsConfig[$key]);
        }
        $imgsKeys = array('plus', 'minus', 'parent', 'parentOpen', 'item');
        $jsConfig['images'] = array();
        foreach ($imgsKeys as $img) {
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
        // level|text|href|title|icon|target|expanded
        $keys = array('level', 'name', 'href', 'title', 'icon', 'target', 'expanded');
        // id parent_id name title icon class active expanded href
        $lines = explode("\n", trim($menuString));
        $levels = array();
        foreach ($lines as $id => $line) {
            $line = array_combine($keys, explode('|', trim($line)));
            $line['id'] = $id + 1;
            $line['level'] = strlen($line['level']);
            $line['parent_id'] = isset($levels[$line['level'] - 1]) ? $levels[$line['level'] - 1] : $this->config['nullParent'];
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
        $objid = $this->config['objid'];

        $map = array();
        $parents = array();

        $this->tree = array();
        foreach ($this->data as $item) {
            if (!isset($item[$objid])) {
                continue;
            }
            // process nested sets
            if ($this->config['nestedSet']) {
                if ((string)$item['level'] == 0) {
                    $parents[0] = $item[$objid];
                } else {
                    $item['parent_id'] = $parents[$item['level'] - 1];
                }
                $parents[$item['level']] = $item[$objid];
            }

            $nodes = isset($item['nodes']) ? $item['nodes'] : array();
            foreach ($nodes as $k => $node) {
                $node = isset($node['item']) ? $node['item'] : $node;
                $nodes[$k]['item'] = array(
                    'active' => isset($node['active']) ? $node['active'] : true,
                    'icon'   => isset($node['icon']) ? $node['icon'] : null,
                    'class'  => isset($node['class']) ? $node['class'] : null,
                    'title'  => isset($node['title']) ? $node['title'] : null,
                    'name'   => isset($node['name']) ? $node['name'] : null,
                    'href'   => isset($node['href']) ? $node['href'] : '#'
                );
            }

            $item  = array(
                'id'        => $item[$objid],
                'parent_id' => isset($item['parent_id']) ? $item['parent_id'] : $this->config['nullParent'],
                'expanded'  => isset($item['expanded']) ? $item['expanded'] : null,
                'active'    => isset($item['active']) ? $item['active'] : true,
                'icon'      => isset($item['icon']) ? $item['icon'] : null,
                'class'     => isset($item['class']) ? $item['class'] : null,
                'title'     => isset($item['title']) ? $item['title'] : null,
                'name'      => isset($item['name']) ? $item['name'] : null,
                'href'      => isset($item['href']) ? $item['href'] : '#'
            );
            $node = array('item' => $item, 'nodes' => $nodes);

            if ((string)$item['parent_id'] == (string)$this->config['nullParent']) {
                $this->tree[$item['id']] = $node;
                $path = null;
            } else {
                $path   = $map[$item['parent_id']];
                $path[] = $item['parent_id'];
                $handle = &$this->tree;
                while (list($key, $value) = each($path)) {
                    if ($value === $this->config['nullParent']) {
                        continue;
                    }
                    $handle = &$handle[$value]['nodes'];
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
     * @param array   $tree        Tree data.
     * @param integer $indentLevel Level of indent to use.
     * @param string  $treeId      Tree Id.
     * @param boolean $root        Is this root level.
     *
     * @return string HTML output.
     */
    protected function _toHTML($tree, $indentLevel = 0, $treeId = null, $root = false)
    {
        $liHtml = array();
        $size = count($tree);
        $i = 1;
        foreach ($tree as $id => $tab) {
            if (!$this->config['renderRoot'] && $root) {
                if (!empty($tab['nodes'])) {
                    $liHtml[] = $this->_toHTML($tab['nodes'], $indentLevel + 1, $treeId);
                }
            } else {
                $subhtml  = !empty($tab['nodes']) ? $this->_toHTML($tab['nodes'], $indentLevel + 2) : '';
                $liHtml[] = $this->_nodeToHTML($id, $tab, $size, $i, $subhtml, $indentLevel + 1);
            }
            $i++;
        }

        $liHtml = implode("\n", $liHtml);
        if ($root && (!$this->config['withWraper'] || !$this->config['renderRoot'])) {
            $html = $liHtml;
        } else {
            $indent  = str_repeat(' ', $indentLevel * 4);
            $ulID    = !empty($treeId) ? ' id="'.$treeId.'"' : '';
            $ulClass = !empty($this->config['treeClass']) ? ' class="'.$this->config['treeClass'].'"' : '';
            $html    = "{$indent}<ul{$ulID}{$ulClass}>\n{$liHtml}\n{$indent}</ul>\n";
        }

        return $html;
    }

    /**
     * Parse single tree node to HTML
     *
     * @param int     $id          Node id.
     * @param array   $tab         Node data.
     * @param int     $size        Tree size.
     * @param int     $i           Current node index.
     * @param string  $nodeSub     HTML code for subnodes if node has such, default null.
     * @param integer $indentLevel Level of indent to use.
     *
     * @return string Node HTML code
     */
    protected function _nodeToHTML($id, $tab, $size, $i, $nodeSub = null, $indentLevel = 1)
    {
        $item   = $tab['item'];
        $indent = str_repeat(' ', ($indentLevel + 1) * 4);

        $toggle = $indent.'<img class="'.$this->config['toggler'].'" alt="" src="'.$this->config['imagesDir'].$this->config['minus'].'" />';

        $iconImage = !empty($item['icon']) ? $item['icon'] : (!empty($tab['nodes']) ?  $this->config['parentOpen'] : $this->config['item']);
        $icon      = $indent.'<img class="'.$this->config['icon'].'" alt="" src="'.$this->config['imagesDir'].$iconImage.'" />';

        $linkClass = $item['active'] == 1 ? $item['class'] : $this->config['nodeUnactive'].' '.$item['class'];
        $linkClass = !empty($linkClass) ? ' class="'.$linkClass.'"' : '';
        $linkHref  = 'href="'.DataUtil::formatForDisplay($item['href']).'"';
        $linkTitle = !empty($item['title']) ? ' title="'.$item['title'].'"' : '';
        $link      = $indent."<a {$linkHref}{$linkClass}{$linkTitle}>{$item['name']}</a>";

        $liId    = !empty($this->config['nodePrefix']) ? ' id="'.$this->config['nodePrefix'].$id.'"' : '';
        $liClass = array();
        $liClass[] = $size == 1 ? $this->config['nodeSingle'] : '';
        $liClass[] = ($i == 1 && $size > 1) ? $this->config['nodeFirst'] : '';
        $liClass[] = ($i == $size && $size > 1) ? $this->config['nodeLast'] : '';
        $liClass[] = !empty($tab['nodes']) ? $this->config['nodeParent'] : $this->config['nodeLeaf'];
        $liClass[] = isset($item['class']) ? $item['class'] : '';
        $liClass = trim(implode(' ', array_filter($liClass)));
        $liClass = ' class="'.$liClass.'"';

        $indent = str_repeat(' ', $indentLevel * 4);

        return "{$indent}<li{$liId}{$liClass}>\n{$toggle}\n{$icon}\n{$link}\n{$nodeSub}{$indent}</li>";
    }
}
