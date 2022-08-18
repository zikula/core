<?php

declare(strict_types=1);

/**
 * Copyright Zikula.
 *
 * This work is contributed to the Zikula under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT.
 * @package Zikula
 * @author Craig Heydenburg
 *
 * Please see the LICENSE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\Wizard;

interface StageInterface
{
    /**
     * The stage name
     */
    public function getName(): string;

    /**
     * The stage's full template name, e.g. 'AcmeDemoBundle:Stage:prep.html.twig'
     */
    public function getTemplateName(): string;

    /**
     * Logic to determine if the stage is required or can be skipped
     *
     * @throws AbortStageException
     */
    public function isNecessary(): bool;

    /**
     * An array of template parameters required in the stage template
     */
    public function getTemplateParams(): array;
}
