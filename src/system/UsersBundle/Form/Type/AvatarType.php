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

namespace Zikula\UsersBundle\Form\Type;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvatarType extends AbstractType
{
    private string $avatarPath;

    public function __construct(
        private readonly array $avatarConfig,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectDir
    ) {
        $this->avatarPath = $projectDir . '/' . $avatarConfig['image_path'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
            'required' => false,
        ];

        if (!$this->allowUploads()) {
            // choice mode
            $choices = [
                'Blank' => 'blank.jpg',
                'Gravatar' => $this->avatarConfig['default_image'],
            ];

            if (file_exists($this->avatarPath)) {
                $finder = new Finder();
                $finder->files()->in($this->avatarPath)->notName('blank.jpg')->notName($this->avatarConfig['default_image'])->sortByName();

                foreach ($finder as $file) {
                    $fileName = $file->getFilename();
                    $choices[$fileName] = $fileName;
                }
            }

            $defaults = array_merge($defaults, [
                'choices' => $choices,
                'attr' => [
                    'class' => 'avatar-selector',
                ],
                'placeholder' => false,
            ]);
        } else {
            // upload mode
            $uploadSettings = $this->avatarConfig['uploads'];
            $defaults['data_class'] = null; // allow string values instead of File objects

            $defaults['help'] = [
                'Possible extensions: %extensions%',
                'Max. file size: %maxSize% bytes',
                'Max. dimensions: %maxWidth%x%maxHeight% pixels',
            ];
            $defaults['help_translation_parameters'] = [
                '%extensions%' => implode(', ', ['gif', 'jpeg', 'jpg', 'png']),
                '%maxSize%' => $uploadSettings['max_size'],
                '%maxWidth%' => $uploadSettings['max_width'],
                '%maxHeight%' => $uploadSettings['max_height'],
            ];
        }

        $resolver->setDefaults($defaults);
    }

    public function getBlockPrefix(): string
    {
        return 'zikulausersbundle_avatar';
    }

    public function getParent(): ?string
    {
        return $this->allowUploads() ? FileType::class : ChoiceType::class;
    }

    /**
     * Checks if uploads or choices should be used.
     */
    private function allowUploads(): bool
    {
        $allowUploads = $this->avatarConfig['uploads']['enabled'];
        if (!$allowUploads) {
            return false;
        }
        if (!file_exists($this->avatarPath) || !is_readable($this->avatarPath) || !is_writable($this->avatarPath)) {
            return false;
        }

        return true;
    }
}
