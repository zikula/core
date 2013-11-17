<?php

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel as BaseContainerAwareHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ContainerAwareHttpKernel extends BaseContainerAwareHttpKernel
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        // determine if this is legacy or not
        $module = $request->attributes->get('_module');
        $bundle = \ModUtil::getModule($module);
        if (null !== $bundle) {
//            return; // this is a bundle based module and can be routed by Symfony.
        }
        // this is a legacy based module

        return parent::handle($request, $type, $catch);
        try {
            return $this->handleRaw($request, $type);
        } catch (\Exception $e) {
            if (false === $catch) {
                $this->finishRequest($request, $type);

                throw $e;
            }

            return $this->handleException($e, $request, $type);
        }
    }
}
