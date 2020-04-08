<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

/*
 * This file is part of the <?php echo $namespace; ?> package.
 *
 * Copyright <?php echo $vendor; ?>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace <?php echo $namespace; ?>;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class <?php echo $name; ?> extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }
}
