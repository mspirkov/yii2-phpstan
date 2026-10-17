<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

use PHPStan\Testing\TypeInferenceTestCase;

final class ContainerGetDynamicReturnTypeExtensionTest extends TypeInferenceTestCase
{
    /**
     * @dataProvider dataFiles
     */
    public function testFileAsserts(string $file): void
    {
        foreach (self::gatherAssertTypes($file) as $assert) {
            $this->assertFileAsserts(...$assert);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function dataFiles(): iterable
    {
        yield 'common' => [__DIR__ . '/Data/ContainerGet/common/code.php'];
        yield 'frontend' => [__DIR__ . '/Data/ContainerGet/frontend/code.php'];
        yield 'frontend api module' => [__DIR__ . '/Data/ContainerGet/frontend/modules/api/code.php'];
        yield 'backend' => [__DIR__ . '/Data/ContainerGet/backend/code.php'];
        yield 'unscoped' => [__DIR__ . '/Data/ContainerGet/unscoped.php'];
    }

    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [
            __DIR__ . '/../../extension.neon',
            __DIR__ . '/Config/ContainerGet/config.neon',
        ]);
    }
}
