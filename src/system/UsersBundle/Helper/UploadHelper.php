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

namespace Zikula\UsersBundle\Helper;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadHelper
{
    /**
     * List of allowed file extensions
     */
    private array $imageExtensions;

    private string $avatarPath;

    public function __construct(
        private readonly array $uploadConfig,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectDir,
        string $imagePath
    ) {
        $this->imageExtensions = ['gif', 'jpeg', 'jpg', 'png'];
        $this->avatarPath = $projectDir . '/' . $imagePath;
    }

    /**
     * Process a given upload file.
     */
    public function handleUpload(UploadedFile $file, int $userId = 0): string
    {
        $allowUploads = $this->uploadConfig['enabled'];
        if (!$allowUploads) {
            return '';
        }
        if (!file_exists($this->avatarPath) || !is_readable($this->avatarPath) || !is_writable($this->avatarPath)) {
            return '';
        }

        if (UPLOAD_ERR_OK !== $file->getError()) {
            return '';
        }
        if (!is_numeric($userId) || $userId < 1) {
            return '';
        }

        $shrinkImages = $this->uploadConfig['shrink_large_images'];
        $filePath = $file->getRealPath();

        // check for file size limit
        if (!$shrinkImages && filesize($filePath) > $this->uploadConfig['max_size']) {
            unlink($filePath);

            return '';
        }

        // Get image information
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            // file is not an image
            unlink($filePath);

            return '';
        }

        $extension = image_type_to_extension($imageInfo[2], false);
        // check for image type
        if (!in_array($extension, $this->imageExtensions, true)) {
            unlink($filePath);

            return '';
        }

        // check for image dimensions limit
        $isTooLarge = $imageInfo[0] > $this->uploadConfig['max_width'] || $imageInfo[1] > $this->uploadConfig['max_height'];

        if ($isTooLarge && !$shrinkImages) {
            unlink($filePath);

            return '';
        }

        // everything's OK, so move the file
        $avatarFileNameWithoutExtension = 'pers_' . $userId;
        $avatarFileName = $avatarFileNameWithoutExtension . '.' . $extension;
        $avatarFilePath = $this->avatarPath . '/' . $avatarFileName;

        // delete old user avatar
        foreach ($this->imageExtensions as $ext) {
            $oldFilePath = $this->avatarPath . '/' . $avatarFileNameWithoutExtension . '.' . $ext;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        $file->move($this->avatarPath, $avatarFileName);

        if ($isTooLarge && $shrinkImages) {
            $imagine = new Imagine();
            $image = $imagine->open($avatarFilePath);
            $image->resize(new Box($this->uploadConfig['max_width'], $this->uploadConfig['max_height']))
                  ->save($avatarFilePath);
        }

        chmod($avatarFilePath, 0644);

        return $avatarFileName;
    }
}
