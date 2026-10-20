<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

final class ContainerGetDynamicReturnTypeExtensionTest extends AbstractTestCase
{
    public static function dataFiles(): iterable
    {
        yield 'common' => [__DIR__ . '/Data/ContainerGet/common/code.php'];
        yield 'frontend' => [__DIR__ . '/Data/ContainerGet/frontend/code.php'];
        yield 'frontend config' => [__DIR__ . '/Data/ContainerGet/frontend/config/container.php'];
        yield 'frontend api module' => [__DIR__ . '/Data/ContainerGet/frontend/modules/api/code.php'];
        yield 'backend' => [__DIR__ . '/Data/ContainerGet/backend/code.php'];
        yield 'unscoped' => [__DIR__ . '/Data/ContainerGet/unscoped.php'];
    }

    protected static function getAdditionalConfigFile(): string
    {
        return __DIR__ . '/Config/ContainerGet/config.neon';
    }
}
