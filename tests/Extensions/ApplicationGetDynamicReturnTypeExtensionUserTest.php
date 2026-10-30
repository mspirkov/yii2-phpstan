<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

final class ApplicationGetDynamicReturnTypeExtensionUserTest extends AbstractTestCase
{
    public static function dataFiles(): iterable
    {
        yield 'identity class' => [__DIR__ . '/Data/Application/user/identity/get.php'];
    }

    protected static function getAdditionalConfigFile(): string
    {
        return __DIR__ . '/Config/Application/user.neon';
    }
}
