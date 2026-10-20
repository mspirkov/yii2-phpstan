<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

final class ApplicationPropertyExpressionTypeResolverExtensionTest extends AbstractTestCase
{
    public static function dataFiles(): iterable
    {
        yield 'frontend' => [__DIR__ . '/Data/Application/frontend/properties.php'];
        yield 'backend' => [__DIR__ . '/Data/Application/backend/properties.php'];
        yield 'common config' => [__DIR__ . '/Data/Application/common/config/main.php'];
        yield 'shared module' => [__DIR__ . '/Data/Application/modules/shop/properties.php'];
    }

    protected static function getAdditionalConfigFile(): string
    {
        return __DIR__ . '/Config/Application/config.neon';
    }
}
