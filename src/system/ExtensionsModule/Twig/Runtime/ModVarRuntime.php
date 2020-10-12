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

namespace Zikula\ExtensionsModule\Twig\Runtime;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class ModVarRuntime implements RuntimeExtensionInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->translator = $translator;
        $this->variableApi = $variableApi;
    }

    public function getModVar(string $module, string $name, $default = null)
    {
        if (empty($module) || empty($name)) {
            throw new InvalidArgumentException($this->translator->trans('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->variableApi->get($module, $name, $default);
    }

    public function getSystemVar(string $name, $default = null)
    {
        if (empty($name)) {
            throw new InvalidArgumentException($this->translator->trans('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->variableApi->getSystemVar($name, $default);
    }
}
