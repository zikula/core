<?php //

namespace Zikula\Bundle\CoreBundle;

use Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\RegisterCoreListenersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Scope;
//use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

//        $container->addScope(new Scope('request'));
        $container->addCompilerPass(new RegisterCoreListenersPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
