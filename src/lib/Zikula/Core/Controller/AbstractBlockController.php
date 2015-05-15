<?php

namespace Zikula\Core\Controller;

abstract class AbstractBlockController extends AbstractController
{
    /**
     * Initialise interface.
     *
     * @return void
     */
    abstract public function init();

    /**
     * Get info interface.
     *
     * @return array BlockInfo.
     */
    abstract public function info();

    /**
     * Display block.
     *
     * @param array $blockInfo blockInfo.
     *
     * @return array blockInfo.
     */
    abstract public function display($blockInfo);

    /**
     * Modify block interface.
     *
     * @param array $blockInfo Block info.
     *
     * @return string
     */
    public function modify($blockInfo)
    {
        return '';
    }

    /**
     * Update block interface.
     *
     * @param array $blockInfo Block info.
     *
     * @return array blockInfo.
     */
    public function update($blockInfo)
    {
        return $blockInfo;
    }

}
