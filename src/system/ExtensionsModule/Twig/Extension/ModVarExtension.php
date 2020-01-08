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

namespace Zikula\ExtensionsModule\Twig\Extension;

use InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class ModVarExtension extends AbstractExtension
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

    public function getFunctions()
    {
        return [
            new TwigFunction('getModVar', [$this, 'getModVar']),
            new TwigFunction('getSystemVar', [$this, 'getSystemVar'])
        ];
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getModVar(string $module, string $name, $default = null)
    {
        if (empty($module) || empty($name)) {
            throw new InvalidArgumentException($this->translator->trans('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->variableApi->get($module, $name, $default);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getSystemVar(string $name, $default = null)
    {
        if (empty($name)) {
            throw new InvalidArgumentException($this->translator->trans('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->variableApi->getSystemVar($name, $default);
    }
}
