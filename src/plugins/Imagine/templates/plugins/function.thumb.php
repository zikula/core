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

/**
 * Available params:
 *  - image         (string)        Path to source image (required)
 *  - width         (int)           Thumbnail width in pixels (optional, default value based on 'default' preset)
 *  - height        (int)           Thumbnail width in pixels (optional, default value based on 'default' preset)
 *  - mode          (string)        Thumbnail mode; 'inset' or 'outset' (optional, default 'inset')
 *  - extension     (string)        File extension for thumbnails: jpg, png, gif; null for original file type
 *                                  (optional, default value based on 'default' preset)
 *  - objectid      (string)        Unique signature for object, which owns this thumbnail (optional)
 *  - preset        (string|object) Name of preset defined in Imagine or custom preset passed as instance of
 *                                  SystemPlugin_Imagine_Preset; if given inline options ('width', 'heigth', 'mode'
 *                                  and 'extension') are ignored (optional)
 *  - manager       (object)        Instance of SystemPlugin_Imagine_Manager; if given inline options ('width',
 *                                  'heigth', 'mode' and 'extension') are ignored (optional)
 *  - fqurl         (boolean)       If set the thumb path is absolute, if not relative
 *  - tag           (boolean)       If set to true - full <img> tag will be generated. Tag attributes should be
 *                                  passed with "img_" prefix (for example: "img_class"). Getttext prefix may be
 *                                  used for translations (for example: "__img_alt")
 *
 * Examples
 *
 * Basic usage with inline options:
 *  {thumb
 *      image='path/to/image.png'
 *      width=100
 *      height=100
 *      mode='inset'
 *      extension='jpg'
 *  }
 *
 * Using preset define in Imagine plugin
 *  {thumb
 *      image='path/to/image.png'
 *      objectid='123'
 *      preset='my_preset'
 *  }
 *
 * Using custom preset, defined in module and passed to template
 *  {thumb
 *      image='path/to/image.png'
 *      objectid='123'
 *      preset=$preset
 *  }
 *
 * Using custom SystemPlugin_Imagine_Manager instance, defined in module and passed to template
 *  {thumb
 *      image='path/to/image.png'
 *      objectid='123'
 *      manager=$manager
 *  }
 *
 * Generating full img tag
 *  {thumb
 *      image='path/to/image.png'
 *      objectid='123'
 *      preset=$preset
 *      tag=true
 *      __img_alt='Alt text, gettext prefix may be used'
 *      img_class='image-class'
 *  }
 * This will generate:
 *  <img src="thumb/path" widht="100" height="100" alt="Alt text, gettext prefix may be used" class="image-class" />
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string thumb path
 */
function smarty_function_thumb($params, Zikula_View $view)
{
    if (!isset($params['image']) || empty($params['image'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_thumb', 'image')));
        return false;
    }

    $image = $params['image'];
    $objectId = isset($params['objectid']) ? $params['objectid'] : null;

    if (isset($params['manager']) && $params['manager'] instanceof SystemPlugin_Imagine_Manager) {
        $manager = $params['manager'];
    } else {
        $manager = $view->getServiceManager()->getService('systemplugin.imagine.manager');
    }

    if (isset($params['preset']) && $params['preset'] instanceof SystemPlugin_Imagine_Preset) {
        $preset = $params['preset'];
    } elseif (isset($params['preset']) && $manager->getPlugin()->hasPreset($params['preset'])) {
        $preset = $manager->getPlugin()->getPreset($params['preset']);
    } else {
        $preset = array();
        $preset['width'] = isset($params['width']) ? $params['width'] : null;
        $preset['height'] = isset($params['height']) ? $params['height'] : null;
        $preset['mode'] = isset($params['mode']) ? $params['mode'] : null;
        $preset['extension'] = isset($params['extension']) ? $params['extension'] : null;
        $preset = array_filter($preset);
    }

    $manager->setPreset($preset);
    $thumb = $manager->getThumb($image, $objectId);

    $basePath = (isset($params['fqurl']) && $params['fqurl']) ? System::getBaseUrl() : System::getBaseUri();
    $result = "{$basePath}/{$thumb}";

    if (isset($params['tag']) && $params['tag']) {
        $thumbSize = @getimagesize($thumb);
        $attributes = array();
        $attributes[] = "src=\"{$basePath}/{$thumb}\"";
        $attributes[] = $thumbSize[3]; // width and height
        // get tag params
        foreach ($params as $key => $value) {
            if (strpos($key, 'img_') === 0) {
                $key = str_replace('img_', '', $key);
                $attributes[$key] = "{$key}=\"{$value}\"";
            }
        }
        if (!isset($attributes['alt'])) {
            $attributes[] = 'alt=""';
        }
        $attributes = implode(' ', $attributes);
        $result = "<img {$attributes} />";
    }

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
