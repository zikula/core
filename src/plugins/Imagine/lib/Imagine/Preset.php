<?php
/**
 * Copyright Zikula Foundation 2012 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version.
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class SystemPlugin_Imagine_Preset extends ArrayObject
{
    /**
     * Preset name
     *
     * @var string
     */
    protected $name;

    /**
     * Creates new preset object.
     *
     * It can be construct in several ways:
     * - with name and data for normal usage
     * - only with data as "anonymous" preset (it name will be set to width + x + height)
     * - without args for empty preset template
     *
     * @param string|array  $preset Preset name or array with preset data for "anonymous" preset
     * @param array         $data   Preset data
     */
    public function __construct($preset = null, $data = null)
    {
        if (is_string($preset)) {
            $array = $data;
            $this->name = $preset;
        } else {
            $array = $preset;
        }
        $array = $this->prepare($array);

        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Sets preset name.
     *
     * Called without args will set name to width + x + height
     *
     * @param string    $name   Preset name
     *
     * @return SystemPlugin_Imagine_Preset
     */
    public function setName($name = null)
    {
        if (is_null($name)) {
            $this->name = "{$this['width']}x{$this['height']}";
        } else {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * Gets preset name.
     *
     * @return string
     */
    public function getName()
    {
        if (is_null($this->name)) {
            $this->setName();
        }

        return $this->name;
    }

    /**
     * Extends input data with required entries.
     *
     * @param array $data   Preset data
     *
     * @return array
     */
    private function prepare($data)
    {
        $array = array_merge($this->getEmptyPreset(), (array)$data);

        return $array;
    }

    /**
     * Gets required preset entries.
     *
     * @return array
     */
    private function getEmptyPreset()
    {
        return array(
            'width' => 100,
            'height' => 100,
            'mode' => null,
            'extension' => null,
            '__module' => null,
            '__imagine' => null,
            '__transformation' => null
        );
    }
}