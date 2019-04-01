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

namespace Zikula\ExtensionsModule\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\ExtensionsModule\Helper\ComposerValidationHelper;
use Zikula\ExtensionsModule\ZikulaExtensionsModule;

class ComposerValidationHelperTest extends TestCase
{
    /**
     * @var ComposerValidationHelper
     */
    protected $validationHelper;

    protected function setUp(): void
    {
        $extModule = $this
            ->getMockBuilder(ZikulaExtensionsModule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extModule->method('getPath')
            ->willReturn(realpath(__DIR__ . '/../../'));
        $kernel = $this
            ->getMockBuilder(ZikulaHttpKernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $kernel->method('getModule')
            ->willReturn($extModule);
        $this->validationHelper = new ComposerValidationHelper($kernel, new IdentityTranslator());
    }

    /**
     * @covers ComposerValidationHelper::check()
     * @dataProvider getFileNamesProvider
     */
    public function testCheck(string $fileName, bool $isValid, array $errors): void
    {
        $file = $this->getSplFileInfo($fileName);
        $this->assertNotEmpty($file);
        $this->validationHelper->check($file);
        $this->assertEquals($isValid, $this->validationHelper->isValid());
        $this->assertEquals($errors, $this->validationHelper->getErrors());
    }

    public function getFileNamesProvider(): array
    {
        return [
            /** fileName, isValid, errors[] */
            ['minimum_composer.json', true, []],
            ['maximum_composer.json', true, []],
            ['minimum_error1_composer.json', false, ['Error found in composer file of Fixtures (/../Fixtures) in property "description": The property description is required.']],
            ['minimum_error2_composer.json', false, ['Error found in composer file of Fixtures (/../Fixtures) in property "autoload.psr-4": The property psr-4 is required.']],
            ['minimum_syntax_error_composer.json', false, ['Unable to decode composer file of Fixtures (/../Fixtures): Syntax error. Ensure the composer.json file has a valid syntax.']],
            ['empty_composer.json', false, [
                'Error found in composer file of Fixtures (/../Fixtures) in property "name": The property name is required.',
                'Error found in composer file of Fixtures (/../Fixtures) in property "description": The property description is required.',
                'Error found in composer file of Fixtures (/../Fixtures) in property "type": The property type is required.',
                'Error found in composer file of Fixtures (/../Fixtures) in property "license": The property license is required.',
                'Error found in composer file of Fixtures (/../Fixtures) in property "authors": The property authors is required.',
                'Error found in composer file of Fixtures (/../Fixtures) in property "require": The property require is required.',
                'Error found in composer file of Fixtures (/../Fixtures) in property "extra": The property extra is required.',
                'Error found in composer file of Fixtures (/../Fixtures) in property "autoload": The property autoload is required.',
            ]],
        ];
    }

    private function getSplFileInfo($file): SplFileInfo
    {
        return new SplFileInfo(realpath(__DIR__ . '/../Fixtures/' . $file), '/../Fixtures', '/../Fixtures/' . $file);
    }
}
