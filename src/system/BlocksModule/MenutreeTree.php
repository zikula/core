<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule;

use ModUtil;
use PageUtil;
use DataUtil;

/**
 * Zikula_Tree class.
 */
class MenutreeTree extends \Zikula_Tree
{
    /**
     * Constructor.
     *
     * @param array $config Config array
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        $config = array_merge($config, [
            'langs'        => ['en'],
            'flat'         => false,
            'parseURL'     => false,
            'sortable'     => false,
            'dynamicClass' => 'z-tree-dynamic',
            'imagesDir'    => 'system/BlocksModule/Resources/public/images/menutree/',
        ]);
        parent::__construct($config);
    }

    /**
     * Parse data
     *
     * @return array Tree data
     */
    protected function _parseData()
    {
        $this->tree = [];
        $map = [];

        $langs = $this->config['langs'];
        $reflang = $langs[0];

        foreach ($this->data as $a) {
            $item = [];

            foreach ((array)$langs as $lang) {
                if (empty($a[$lang])) {
                    if (!empty($a[$reflang])) {
                        $_item = $a[$reflang];
                    } else {
                        $_item = current($a);
                    }
                    $_item['state'] = 0;
                    $_item['lang']  = $lang;
                } else {
                    $_item = $a[$lang];
                }

                $item[$lang] = [
                    'id'        => isset($_item['id']) ? $_item['id'] : null,
                    'parent_id' => isset($_item['parent']) ? $_item['parent'] : $this->config['nullParent'],
                    'name'      => isset($_item['name']) ? $_item['name'] : null,
                    'title'     => isset($_item['title']) ? $_item['title'] : null,
                    'icon'      => isset($_item['icon']) ? $_item['icon'] : null,
                    'class'     => isset($_item['className']) ? $_item['className'] : null,
                    'active'    => isset($_item['state']) ? $_item['state'] : true,
                    'expanded'  => isset($_item['expanded']) ? $_item['expanded'] : null,
                    'href'      => isset($_item['href']) ? $_item['href'] : '',
                    'lang'      => isset($_item['lang']) ? $_item['lang'] : '',
                    'dynamic'   => strpos($_item['href'], '{ext:') === 0,
                ];
                if ($this->config['parseURL']) {
                    $item[$lang]['href'] = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'encodebracketurl', $item[$lang]['href']);
                }
            }

            if ($this->config['flat']) {
                $_node = [
                    'item' => $item[$reflang],
                    'nodes' => []
                ];
            } else {
                $_node = [
                    'item' => $item,
                    'nodes' => []
                ];
            }

            if ($a[$reflang]['parent'] == 0) {
                $this->tree[$a[$reflang]['id']] = $_node;
                $path = null;
            } else {
                $path   = $map[$a[$reflang]['parent']];
                $path[] = $a[$reflang]['parent'];
                $handle = &$this->tree;
                while (list($key, $value) = each($path)) {
                    if ($value === 0) {
                        continue;
                    }
                    $handle = &$handle[$value]['nodes'];
                }
                $handle[$a[$reflang]['id']] = $_node;
            }

            $map[$a[$reflang]['id']] = $path;
        }

        return $this->tree;
    }

    /**
     * Get decoded, structured data
     *
     * @return string HTML output
     */
    public function getData()
    {
        return $this->tree;
    }

    /**
     * Get HTML output
     *
     * @return string HTML output
     */
    public function getHTML()
    {
        PageUtil::addVar('stylesheet', $this->config['cssFile']);
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'livepipe');
        PageUtil::addVar('javascript', 'zikula.tree');
        PageUtil::addVar('javascript', 'system/BlocksModule/Resources/public/js/Zikula.Menutree.Tree.js');
        $jsClass = 'Zikula.Menutree.Tree';
        $initScript = "
        <script type=\"text/javascript\">
            document.observe('dom:loaded', function() {
                {$jsClass}.add('{$this->config['id']}', '{$this->getConfigForScript()}');
            });
        </script>";
        PageUtil::addVar('header', $initScript);

        $tree = $this->_toHTML($this->tree, 1, $this->config['id']);

        $wraperClass = !empty($this->config['wraperClass']) ? 'class="'.$this->config['wraperClass'].'"' : '';
        $this->html  = "\n<div {$wraperClass}>\n{$tree}</div>";

        return $this->html;
    }

    /**
     * Parse single tree node to HTML
     *
     * @param int    $id          Node id
     * @param array  $tab         Node data
     * @param int    $size        Tree size
     * @param int    $i           Current node index
     * @param string $nodeSub     HTML code for subnodes if node has such, default null
     * @param int    $indentLevel The level to indent the node
     *
     * @return string Node HTML code
     */
    protected function _nodeToHTML($id, $tab, $size, $i, $nodeSub = null, $indentLevel = 1)
    {
        $langs = $this->config['langs'];
        $reflang = $langs[0];
        $isDynamic = false;

        $item   = $tab['item'];
        $indent = str_repeat(' ', ($indentLevel + 1) * 4);

        $toggle = $indent.'<img class="'.$this->config['toggler'].'" alt="" src="'.$this->config['imagesDir'].$this->config['minus'].'" />';

        $iconImage = !empty($item[$reflang]['icon']) ? $item[$reflang]['icon'] : $this->config['item'];
        $iconImage = !empty($tab['nodes']) ? $this->config['parentOpen'] : $iconImage;
        $icon      = $indent.'<img class="'.$this->config['icon'].'" alt="" src="'.$this->config['imagesDir'].$iconImage.'" />';

        $links = [];
        foreach ($item as $lang => $translated) {
            $isDynamic = $isDynamic || $translated['dynamic'];
            $linkClass = $translated['active'] == 1 ? $translated['class'] : $this->config['nodeUnactive'].' '.$translated['class'];
            $linkClass = !empty($linkClass) ? ' class="'.$linkClass.'"' : '';
            $linkLang  = ' lang="'.$translated['lang'].'"';
            $linkHref  = ' href="'.DataUtil::formatForDisplay($translated['href']).'"';
            $linkTitle = !empty($translated['title']) ? ' title="'.$translated['title'].'"' : '';

            $links[] = "<a{$linkHref}{$linkLang}{$linkTitle}{$linkClass}>{$translated['name']}</a>";
        }
        $links = $indent.implode("\n{$indent}", $links);

        $itemid  = $indent.'<span class="sub">'.$translated['id'].'.</span>';

        $liId    = !empty($this->config['nodePrefix']) ? ' id="'.$this->config['nodePrefix'].$id.'"' : '';
        $liClass = [];
        $liClass[] = $isDynamic ? $this->config['dynamicClass'] : '';
        $liClass[] = $size == 1 ? $this->config['nodeSingle'] : '';
        $liClass[] = ($i == 1 && $size > 1) ? $this->config['nodeFirst'] : '';
        $liClass[] = ($i == $size && $size > 1) ? $this->config['nodeLast'] : '';
        $liClass[] = !empty($tab['nodes']) ? $this->config['nodeParent'] : $this->config['nodeLeaf'];
        $liClass = trim(implode(' ', array_filter($liClass)));
        $liClass = ' class="'.$liClass.'"';

        $indent = str_repeat(' ', $indentLevel * 4);

        return "{$indent}<li{$liId}{$liClass}>\n{$toggle}\n{$icon}\n{$itemid}\n{$links}\n{$nodeSub}{$indent}</li>";
    }
}
