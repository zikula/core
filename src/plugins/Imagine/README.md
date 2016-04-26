Imagine plugin usage
--------------------

# Overview
Imagine plugin implements [Imagine image manipulation library](https://github.com/avalanche123/Imagine)
and offers some standardised methods for managing thumbnails.

There is one update done in the Imagine plugin itself, see https://github.com/avalanche123/Imagine/pull/407.
Until that one is Pulled into the offical version this needs to be applied.

# How it works

The main idea of this plugin is to move the thumbnail generation process to the presentation layer.
Therefore, the thumbnails are generated only at the time there is a need to display them.

Imagine allows for generating thumbnails in modules (PHP code) or directly in the template (smarty plugin).
See the appropriate sections with examples.
NOTICE: it is recommended to use only one of these methods (generating thumbs in template or PHP code).

Settings for generated thumbnails can be passed inline during thumbnail request or via presets.
Presets are described in separate section.

The process of generating thumbnails as follows:
- Request for image thumbnail occurs
- Plugin checks if there is already a thumbnail for the image with given parameters (width and height count here)
- If there already exists a thumbnail - the plugin returns the path to it
- If the thumbnail does not exist or the existing thumbnail expired
  (source image was modified after thumbnail was generated) - generate new thumbnail

Imagine allows you to group thumbnails by module and object parameters. Grouping allows then
for selective cleanup/removal thumbnails (for example, all module or object thumbnails).
Grouping parameters available are:
- module - for example "News", optional, if not specified "zikula" is used
- object id - for example "17", optional, if not specified, object id is generated from the source image path

Paths to thumbnails have following format:

    thumbs dir / module / object id / encoded image path / preset.time.ext

With all of the default settings, the path to the thumbnail image 'path/to/image.png' will be as follows:

    ztemp/systemplugin.imagine/zikula/a89fda35/cGF0aC90by9pbWFnZS5wbmc=/default.1352112922.png

where
- systemplugin.imagine - storage dir
- zikula - module
- a89fda35 - md5 hash generated from image path as object id
- cGF0aC90by9pbWFnZS5wbmc= - encoded image path
- default.1352112922.png - build as thumbnail preset name + image modification time + extension

# Usage

## Thumbnail generation in templates

To generate thumbnails in templates, use "thumb" plugin. It may return the thumbnail path
or a full image tag ("img") with all parameters for the generated thumbnail.

Thumbnail options can be passed inline or via preset/manager object.

### Inline options

```
    {thumb image='path/to/image.png' width=100 height=100 mode='inset' extension='png'}
```
	
You specify a width and height or only a width or height, where the other dimension will be calculated from the image ratio.
* width can be a pixel width, specified as 'auto' or omitted in which case auto will be used
* height can be a pixel width, specified as 'auto' or omitted in which case auto will be used
* mode can be inset or outbound or omitted in which case the default of inset will be used
   inset will scale the image to fit while keeping aspect ratio
   outbound will scale to exact dimensions and will crop certain parts of the image
   in outbound mode choosing width or height to auto will give inset behaviour
* extension can be jpg, png, gif or omitted to keep the original file type
* options is an array to specify how to operate on the the images
   * options[jpeg_quality] specifies the jpeg quality from 0-100[%], were 100 is best quality (default 75)
   * options[png_compression_level] specifies the png file compression from 0-9, where 0 is no compression (default 7)
   * options[resolution-x] specifies the DPI value in x-direction e.g. 150 (default is 72)
   * options[resolution-y] specifies the DPI value in the y-direction e.g. 150 (default is 72)
   * options[flatten] boolean to indicate if multi-layer images (animated gif) should be flattened (default true)
   * options[resampling-filter] specifies which filter to use for the operations

### Options passed using preset

```
    {thumb image='path/to/image.png' objectid='123' preset=$preset}
```

In this case "$preset" has to be initiated in the module controller and passed to the template.
"$preset" refers to the name of the preset set in the Imagine system plugin settings.
In a Zikula Imagine preset you set: width, height, mode and the extension.

### Use custom instance of SystemPlugin_Imagine_Manager

```
    {thumb image='path/to/image.png' objectid='123' manager=$manager}
```

In this case "$manager" have to be initiated in module controller and passed to template.

### Generate full image tag

```
    {thumb image='path/to/image.png' objectid='123' preset=$preset tag=true __img_alt='Alt text, gettext prefix may be used' img_class='image-class'}
```

This will generate (width and height from the preset):

```
    <img src="thumb/path" widht="100" height="100" alt="Alt text, gettext prefix may be used" class="image-class" />
```

## Thumbnail generation in PHP code

Imagine plugin exposes "manager" (SystemPlugin_Imagine_Manager) class as registered service.
To access it simply call:

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
```

or

```
    $manager = ServiceUtil::getManager()->get('systemplugin.imagine.manager');
```

### Simple usage - default settings

In the simplest form the only required param for Imagine is source image path. 
All other settings are taken from "default" preset.

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $thumb = $manager->getThumb('path/to/image.png');
```

Generated thumbnail will have fallowing path:

    ztemp/systemplugin.imagine/zikula/a89fda35/cGF0aC90by9pbWFnZS5wbmc=/default.1352112922.png


### Custom preset

Best way for customising thumb settings is using predefined presets:

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $manager->setPreset('my_preset');
    $thumb = $manager->getThumb('path/to/image.png');
```

Generated thumbnail will have fallowing path:

    ztemp/systemplugin.imagine/zikula/a89fda35/cGF0aC90by9pbWFnZS5wbmc=/my_preset.1352112922.png

See Presets section to learn about defining presets.

### Grouping features

Thumbnails generated by Imagine can be grouped by module and object ids (this can be numeric ID
or ony other unique signature). 

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $manager->setModule('News'); // module name can be stored in preset
    $manager->setPreset($preset); // $preset should be instance of SystemPlugin_Imagine_Preset
    $thumb = $manager->getThumb('path/to/image.png', 123);
```

Generated thumbnail will have fallowing path:

    ztemp/systemplugin.imagine/News/123/cGF0aC90by9pbWFnZS5wbmc=/my_preset.1352112922.png

Grouping allows for selective thumbnail cleanup/deleting. For example module can delete object thumbs
when object is deleted. Or cleanup/delete all thumbs for module on user demand or on preset change.
It's recommended to use grouping methods.

### Custom image transformations

It is possible to apply custom image transformations before the thumbnail is created. 
This can be archived by adding transformations to the preset (see Presets section) 
or applying transformation inline:

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $transformation = new Imagine\Filter\Transformation();
    $transformation->flipVertically()->flipHorizontally();

    $manager->setModule('Foo')
        ->setTransformation($transformation)
        ->setPreset('my_preset');
    $thumb = $manager->getThumb('path/to/image.png');
```

By adding transformations with a priority greater than 50, you can also apply 
custom transformations after the thumbnail generation:

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $transformation = new Imagine\Filter\Transformation();
    $transformation->add(new Imagine\Filter\Basic\FlipHorizontally(), 60);

    $manager->setModule('Foo')
        ->setTransformation($transformation)
        ->setPreset('my_preset');
    $thumb = $manager->getThumb('path/to/image.png');
```


### Imagine engines

Imagine library offers standardised interface for different PHP image engines. This may be:
- Imagick
- Gmagick
- GD2
By default Imagine plugin automatically selects first accessible engine (first Imagick, then Gmagick, then GD2).
It's possible to force Imagine plugin to use selected one by defining Imagine object inside preset
(see Preset section) or setting it inline:

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $imagine = new \\Imagine\\Gmagick\\Imagine();

    $manager->setModule('Foo')
        ->setImagine($imagine)
        ->setPreset('my_preset');
    $thumb = $manager->getThumb('path/to/image.png');
```

## Thumbnail cache management

Imagine offers few methods for "cleaning" generated thumbs.
When "cleanup" term is used it means removing thumbnails, which source image was deleted.

### Preset thumb

This routine is used internally for removing outdated thumbs for given image and preset.

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $manager->setModule('News');
    $manager->removePresetThumbs($imagePath, $objectId);
```

### All thumbs for image

This routine allows to remove all thumbs for given image (for example when image is deleted)

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $manager->setModule('News');
    $manager->removeImageThumbs($imagePath, $objectId); // module
```

### All thumbs for object

This routine allows to remove all thumbs for object (for example when object is deleted)

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $manager->setModule('News');
    $manager->removeObjectThumbs($objectId); // module
```

### All thumbs for module

This routine allows to perform "cleanup" for module thumbs

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $manager->setModule('News');
    $manager->cleanupModuleThumbs($force);
```

### All thumbs

This routine performs "cleanup" for all thumbs generated by Imagine

```
    $manager = $this->getContainer()->get('systemplugin.imagine.manager');
    $manager->cleanupThumbs($force)
```

## Presets

Presets are predefined sets of settings for thumbnail generation. There are two types of presets:
    - presets defined inside Imagine plugin (in configuration interface)
    - presets defined and stored by modules itself

Both types of presets are handled by SystemPlugin_Imagine_Preset class.
Preset allows to define:
- width - thumbnail width in pixels
- height - thumbnail height in pixels
- mode - "inset" or "outbound"
- extension - file extension for thumbnails (jpg, png, gif; null for original file type)
- options[jpeg_quality] - value between 0-100%, where 100% is best quality (default 75)
- options[png_compression_level] - value between 0-9, where 0 is no compression (default 7)

There are also special options  which allows to store in preset additional settings for thumbnail manager:
- __module - module name for preset
- __imagine - Imagine object (has to be instance of \Imagine\Image\ImagineInterface)
- __transformation - transformation object (has to be instance of \Imagine\Filter\Transformation)

### Get preset defined inside Imagine plugin 

```
    $plugin = $this->getContainer()->get('systemplugin.imagine');
    $preset = $plugin->getPreset('preset_name');
```

### Define own preset

```
    $name = 'my_preset';
    $data = [
        'width' => 100,
        'heigth' => 100,
        'mode' => 'outbound',
        '__module' => 'Foo',
        '__transformation' => $your_custom_transformations
    ];
    $preset = new SystemPlugin_Imagine_Preset($name, $data);
```

Custom module preset should be stored inside module (presets are serializable).

