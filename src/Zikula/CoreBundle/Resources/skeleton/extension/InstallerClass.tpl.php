<?php echo "<?php\n"; ?>

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

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

class <?php echo $name; ?> extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }
}
