<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Content;

use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ThemeModule\Engine\Asset;

/**
 * Content type base class for convenient implementation.
 */
abstract class AbstractContentType implements ContentTypeInterface
{
    /**
     * Translator instance
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Bundle name
     *
     * @var string
     */
    protected $bundleName;

    /**
     * Translation domain
     *
     * @var string
     */
    protected $domain;

    /**
     * The name of this content type
     *
     * @var string
     */
    protected $name;

    /**
     * Twig template engine
     *
     * @var Environment
     */
    protected $twig;

    /**
     * Twig template loader
     *
     * @var LoaderInterface
     */
    protected $twigLoader;

    /**
     * @var \Zikula\ContentModule\Helper\PermissionHelper
     */
    protected $permissionHelper;

    /**
     * @var Asset
     */
    protected $assetHelper;

    /**
     * Reference to content item instance which allows to access page data,
     * layout area and styling data and other information.
     *
     * @var \Zikula\ContentModule\Entity\ContentItemEntity
     */
    protected $entity;

    /**
     * Reference to the data fields loaded from either default values or the entity.
     *
     * @var array
     */
    protected $data;

    /**
     * AbstractContentType constructor.
     *
     * @param TranslatorInterface $translator       Translator service instance
     * @param Environment         $twig             Twig service instance
     * @param LoaderInterface     $twigLoader       Twig loader service instance
     * @param PermissionHelper    $permissionHelper PermissionHelper service instance
     * @param Asset               $assetHelper      Asset service instance
     */
    public function __construct(
        TranslatorInterface $translator,
        Environment $twig,
        LoaderInterface $twigLoader,
        /*PermissionHelper */$permissionHelper,
        Asset $assetHelper
    ) {
        $this->translator = $translator;

        $nsParts = explode('\\', get_class($this));
        $vendor = $nsParts[0];
        $nameAndType = $nsParts[1];

        $this->bundleName = $vendor . $nameAndType;
        $this->domain = mb_strtolower($this->bundleName);
        $this->name = str_replace('Type', '', array_pop($nsParts));

        $this->twig = $twig;
        $this->twigLoader = $twigLoader;
        $this->permissionHelper = $permissionHelper;
        $this->assetHelper = $assetHelper;

        $this->data = $this->getDefaultData();
    }

    /**
     * Performs a singular translation.
     *
     * @param string $msg String to be translated
     * @param string|null $locale Optional forced locale
     * @return string
     */
    public function __($msg, $locale = null)
    {
        /** @Ignore */
        return $this->translator->__($msg, $this->domain, $locale);
    }

    /**
     * Performs a plural translation.
     *
     * @param string $m1 Singular instance
     * @param string $m2 Plural instance
     * @param integer $n Object count
     * @param string| null $locale Optional forced locale
     * @return string
     */
    public function _n($m1, $m2, $n, $locale = null)
    {
        /** @Ignore */
        return $this->translator->_n($m1, $m2, $n, $this->domain, $locale);
    }

    /**
     * Performs a format singular translation.
     *
     * @param string $msg String to be translated
     * @param string|array $param Format parameters
     * @param string|null $locale Optional forced locale
     * @return string
     */
    public function __f($msg, $param, $locale = null)
    {
        /** @Ignore */
        return $this->translator->__f($msg, $param, $this->domain, $locale);
    }

    /**
     * Performs a format plural translation.
     *
     * @param string $m1 Singular instance
     * @param string $m2 Plural instance
     * @param integer $n Object count
     * @param string|array $param Format parameters
     * @param string|null $locale Optional forced locale
     * @return string
     */
    public function _fn($m1, $m2, $n, $param, $locale = null)
    {
        /** @Ignore */
        return $this->translator->_fn($m1, $m2, $n, $param, $this->domain, $locale);
    }

    /**
     * Returns content item instance.
     *
     * @return \Zikula\ContentModule\Entity\ContentItemEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Sets content item instance.
     *
     * @param \Zikula\ContentModule\Entity\ContentItemEntity $entity
     */
    public function setEntity(/*ContentItemEntity */$entity)
    {
        $this->entity = $entity;

        $this->data = $entity->getContentData();
    }

    /**
     * Returns the bundle name.
     *
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * Returns the name of this content type.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the category of this content type.
     *
     * @return boolean
     */
    public function getCategory()
    {
        return ContentTypeInterface::CATEGORY_INTEGRATION;
    }

    /**
     * Returns the icon name (FontAwesome icon code suffix, e.g. "pencil").
     *
     * @return string
     */
    public function getIcon()
    {
        return 'cube';
    }

    /**
     * Returns the title of this content type.
     *
     * @return string
     */
    public function getTitle()
    {
        return '- ' . $this->__('no title defined') . ' -';
    }

    /**
     * Returns the description of this content type.
     *
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Returns an extended plugin information shown on settings page.
     *
     * @return string
     */
    public function getAdminInfo()
    {
        return '';
    }

    /**
     * Returns whether this content type is active or not.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->permissionHelper->mayReadContentType($this->getName());
    }

    /**
     * Returns the minimum amount of (Bootstrap) grid columns required by this content type.
     * This layout constraint is used during page editing to avoid unwanted shrinking.
     *
     * @return integer
     */
    public function getMinimumAmountOfGridColumns()
    {
        return 2;
    }

    /**
     * Returns an array of data values retrieved from persistence with proper default values.
     * @return array
     */
    public function getDefaultData()
    {
        return [];
    }

    /**
     * Returns a list of translatable field names if any.
     *
     * @return array
     */
    public function getTranslatableDataFields()
    {
        return [];
    }

    /**
     * Returns an array of current data values.
     * @return array
     */
    public function getData()
    {
        return array_merge($this->getDefaultData(), $this->data);
    }

    /**
     * Returns searchable text, that is all the text that is searchable through Zikula's standard
     * search interface. You must strip the text of any HTML tags and other structural information
     * before returning the text. If you have multiple searchable text fields then concatenate all
     * the text from these and return the full string.
     *
     * @return string
     */
    public function getSearchableText()
    {
        return '';
    }

    /**
     * Returns output for normal or editing display.
     *
     * @param boolean $editMode
     * @return string
     */
    public function display($editMode = false)
    {
        $output = '';
        if (!$this->isActive()) {
            return $output;
        }

        $output .= $this->displayStart();
        if (true === $editMode) {
            $output .= $this->displayEditing();
        } else {
            $output .= $this->displayView();
        }
        $output .= $this->displayEnd();

        return $output;
    }

    /**
     * Returns any text displayed before the actual content.
     *
     * Use this method to display styling like float and width for the content.
     * The default implementation adds a generic <div> around the content, but
     * you can choose to override this method in inherited plugins in order to
     * generate more compact HTML where the styling is included in the actual
     * content.
     *
     * @return string The displayed text
     */
    protected function displayStart()
    {
        $classHtml = '';
        $stylingClasses = $this->getEntity()->getStylingClasses();
        if (null !== $stylingClasses && is_array($stylingClasses) && count($stylingClasses) > 0) {
            $classHtml = ' class="' . implode(' ', $stylingClasses) . '"';
        }

        return '<div' . $classHtml . '>' . "\n";
    }

    /**
     * Returns any text displayed after the actual content.
     *
     * @return string The displayed text
     */
    protected function displayEnd()
    {
        return '</div>';
    }

    /**
     * Returns output for normal display.
     *
     * @return string
     */
    public function displayView()
    {
        $templateParameters = $this->getData();
        $templateParameters['contentId'] = null !== $this->getEntity() ? $this->getEntity()->getId() : 0;

        $contentTypeOutput = $this->twig->render($this->getViewTemplatePath(), $templateParameters);

        $outerTemplate = '@ZikulaContentModule/ContentItem/display.html.twig';

        return $this->twig->render($outerTemplate, [
            'contentTypeOutput' => $contentTypeOutput,
            'contentItem' => $this->getEntity()
        ]);
    }

    /**
     * Returns output for display in editing mode.
     *
     * @return string
     */
    public function displayEditing()
    {
        return $this->displayView();
    }

    /**
     * Returns the full path to the template for the display function in 'namespaced' name-style.
     *     e.g. `return '@AcmeMyBundle/ContentType/headingView.html.twig';`
     *
     * @return string
     */
    public function getViewTemplatePath()
    {
        return $this->getTemplatePath('View');
    }

    /**
     * Returns the full name of the edit form's template in 'namespaced' name-style.
     *     e.g. `return '@AcmeMyBundle/ContentType/headingEdit.html.twig';`
     *
     * @return string
     */
    public function getEditTemplatePath()
    {
        return $this->getTemplatePath('Edit');
    }

    /**
     * Tries to resolve a certain template using a given suffix in 'namespaced' name-style
     * and returns the resulting path.
     *
     * @param string $suffix
     * @return string
     */
    protected function getTemplatePath($suffix = '')
    {
        $template = '@' . $this->getBundleName() . '/ContentType/' . lcfirst($this->getName()) . $suffix . '.html.twig';

        if (!$this->twigLoader->exists($template)) {
            throw new \Exception($this->__f('Error! Could not resolve %template% template.', ['%template%' => $template]));
        }

        return $template;
    }

    /**
     * Returns the FqCN of the form class (e.g. return HeadingType::class;)
     *
     * @return string
     */
    public function getEditFormClass()
    {
        return '';
    }

    /**
     * Returns an array of form options.
     *
     * @param string $context The target page context (one of CONTEXT* constants)
     *
     * @return array
     */
    public function getEditFormOptions($context)
    {
        return [
            'context' => $context
        ];
    }

    /**
     * Returns an array of required assets.
     *
     * @param string $context The target page context (one of CONTEXT* constants)
     *
     * @return array
     */
    public function getAssets($context)
    {
        return [
            'css' => [],
            'js' => []
        ];
    }

    /**
     * Returns the name of the JS function to execute or null for nothing.
     * The function must be registered in the global scope and must not expect any arguments.
     *
     * @param string $context The target page context (one of CONTEXT* constants)
     *
     * @return string
     */
    public function getJsEntrypoint($context)
    {
        return null;
    }
}
