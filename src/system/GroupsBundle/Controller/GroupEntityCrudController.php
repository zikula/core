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

namespace Zikula\GroupsBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Zikula\GroupsBundle\Entity\Group;

class GroupEntityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Group::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Group')
            ->setEntityLabelInPlural('Groups')
            ->setPageTitle('index', '%entity_label_plural% list')
            ->setPageTitle('detail', fn (Group $group) => $group->getName())
            ->setPageTitle('edit', fn (Group $group) => sprintf('Editing <strong>%s</strong>', $group->getName()))
//            ->setDateFormat('...')
            // ...
        ;
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
