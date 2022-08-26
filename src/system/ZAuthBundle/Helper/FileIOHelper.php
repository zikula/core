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

namespace Zikula\ZAuthBundle\Helper;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ZAuthBundle\Api\ApiInterface\UserCreationApiInterface;

class FileIOHelper
{
    private array $createdUsers;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserCreationApiInterface $userCreationApi,
        private readonly MailHelper $mailHelper
    ) {
    }

    public function importUsersFromFile(File $file, string $delimiter = ','): string
    {
        // read the file
        if (!$lines = file($file->getPathname())) {
            return $this->translator->trans('Error! It has not been possible to read the import file.');
        }
        $expectedFields = ['uname', 'pass', 'email', 'activated', 'sendmail', 'groups'];
        $firstLineArray = explode($delimiter, str_replace('"', '', trim($lines[0])));
        foreach ($firstLineArray as $field) {
            if (!in_array(mb_strtolower(trim($field)), $expectedFields, true)) {
                return $this->translator->trans('Error! The import file does not have the expected field %s in the first row. Please check your import file.', ['%s' => $field]);
            }
        }
        unset($lines[0]);

        $counter = 1;
        $importValues = [];

        // prepare the array for import
        foreach ($lines as $line) {
            $line = str_replace('"', '', trim($line));
            $lineArray = explode($delimiter, $line);

            // check if the line has all the needed values
            if (count($lineArray) !== count($firstLineArray)) {
                return $this->translator->trans('Error! The number of parameters in line %s is not correct. Please check your import file.', ['%s' => $counter]);
            }
            $importValues[] = array_combine($firstLineArray, $lineArray);
            $counter++;
        }

        if (empty($importValues)) {
            return $this->translator->trans('Error! The import file does not have values.');
        }
        $generateErrorList = function ($errors) {
            $errorList = '';
            foreach ($errors as $error) {
                $errorList .= $error->getMessage() . PHP_EOL;
            }

            return $errorList;
        };

        // validate values and return errors if found
        if (true !== $errors = $this->userCreationApi->isValidUserDataArray($importValues)) {
            return $generateErrorList($errors);
        }

        // create users
        if ($errors = $this->userCreationApi->createUsers($importValues)) {
            if (0 !== count($errors)) {
                return $generateErrorList($errors);
            }
        }

        // finally, persist all the created users
        $this->userCreationApi->persist();
        $this->createdUsers = $this->userCreationApi->getCreatedUsers();

        // send email if indicated
        foreach ($importValues as $importValue) {
            $activated = $importValue['activated'] ?? 1;
            $sendmail = $importValue['sendmail'] ?? 1;
            if ($activated && $sendmail) {
                $templateArgs = [
                    'user' => $importValue
                ];
                $this->mailHelper->sendNotification($importValue['email'], 'importnotify', $templateArgs);
            }
        }

        return '';
    }

    /**
     * @return \Zikula\UsersBundle\Entity\UserEntity[]
     */
    public function getCreatedUsers(): array
    {
        return $this->createdUsers;
    }
}
