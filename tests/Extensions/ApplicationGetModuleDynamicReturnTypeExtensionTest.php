<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

final class ApplicationGetModuleDynamicReturnTypeExtensionTest extends AbstractTestCase
{
    public static function dataFiles(): iterable
    {
        yield 'configured' => [__DIR__ . '/Data/Application/module/configured/get-module.php'];
        yield 'invalid modules' => [__DIR__ . '/Data/Application/module/invalid/get-module.php'];
    }

    protected static function getAdditionalConfigFile(): string
    {
        return __DIR__ . '/Config/Application/module.neon';
    }
}
