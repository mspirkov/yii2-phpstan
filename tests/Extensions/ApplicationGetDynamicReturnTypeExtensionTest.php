<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

final class ApplicationGetDynamicReturnTypeExtensionTest extends AbstractTestCase
{
    public static function dataFiles(): iterable
    {
        yield 'frontend' => [__DIR__ . '/Data/Application/frontend/get.php'];
    }

    protected static function getAdditionalConfigFile(): string
    {
        return __DIR__ . '/Config/Application/config.neon';
    }
}
