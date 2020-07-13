<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\ModuleInterface\Content;

use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
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
     * @var FilesystemLoader
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

    public function __construct(
        TranslatorInterface $translator,
        Environment $twig,
        FilesystemLoader $twigLoader,
        /*PermissionHelper */
        $permissionHelper,
        Asset $assetHelper
    ) {
        $this->translator = $translator;

        $nsParts = explode('\\', get_class($this));
        list($vendor, $nameAndType) = $nsParts;

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
     * Returns content item instance.
     *
     * @return \Zikula\ContentModule\Entity\ContentItemEntity
     */
    public function getEntity()/*: ContentItemEntity*/
    {
        return $this->entity;
    }

    /**
     * Sets content item instance.
     *
     * @param \Zikula\ContentModule\Entity\ContentItemEntity $entity
     */
    public function setEntity(/*ContentItemEntity */$entity): void
    {
        $this->entity = $entity;

        $this->data = $entity->getContentData();
    }

    public function getBundleName(): string
    {
        return $this->bundleName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return ContentTypeInterface::CATEGORY_INTEGRATION;
    }

    public function getIcon(): string
    {
        return 'cube';
    }

    public function getTitle(): string
    {
        return '- ' . $this->translator->trans('no title defined') . ' -';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getAdminInfo(): string
    {
        return '';
    }

    public function isActive(): bool
    {
        return $this->permissionHelper->mayReadContentType($this->getName());
    }

    public function getMinimumAmountOfGridColumns(): int
    {
        return 2;
    }

    public function getDefaultData(): array
    {
        return [];
    }

    public function getTranslatableDataFields(): array
    {
        return [];
    }

    public function getData(): array
    {
        return array_merge($this->getDefaultData(), $this->data);
    }

    public function getSearchableText(): string
    {
        return '';
    }

    /**
     * Returns output for normal or editing display.
     */
    public function display(bool $editMode = false): string
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
     */
    protected function displayStart(): string
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
     */
    protected function displayEnd(): string
    {
        return '</div>';
    }

    public function displayView(): string
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

    public function displayEditing(): string
    {
        return $this->displayView();
    }

    public function getViewTemplatePath(): string
    {
        return $this->getTemplatePath('View');
    }

    public function getEditTemplatePath(): string
    {
        return $this->getTemplatePath('Edit');
    }

    /**
     * Tries to resolve a certain template using a given suffix in 'namespaced' name-style
     * and returns the resulting path.
     */
    protected function getTemplatePath(string $suffix = ''): string
    {
        $template = '@' . $this->getBundleName() . '/ContentType/' . lcfirst($this->getName()) . $suffix . '.html.twig';

        if (!$this->twigLoader->exists($template)) {
            throw new Exception($this->translator->trans('Error! Could not resolve %template% template.', ['%template%' => $template]));
        }

        return $template;
    }

    public function getEditFormClass(): string
    {
        return '';
    }

    public function getEditFormOptions(string $context): array
    {
        return [
            'context' => $context
        ];
    }

    public function getAssets(string $context): array
    {
        return [
            'css' => [],
            'js' => []
        ];
    }

    public function getJsEntrypoint(string $context): ?string
    {
        return null;
    }
}
