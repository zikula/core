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

namespace Zikula\PermissionsBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Zikula\PermissionsBundle\Entity\PermissionEntity;

class PermissionEntityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PermissionEntity::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
