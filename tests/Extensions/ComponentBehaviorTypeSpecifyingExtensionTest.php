<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

final class ComponentBehaviorTypeSpecifyingExtensionTest extends AbstractTestCase
{
    public static function dataFiles(): iterable
    {
        yield 'code' => [__DIR__ . '/Data/ComponentBehavior/code.php'];
    }

    protected static function getAdditionalConfigFile(): ?string
    {
        return null;
    }
}
