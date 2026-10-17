<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

use PHPStan\Testing\TypeInferenceTestCase;

final class ApplicationPropertyExpressionTypeResolverExtensionTest extends TypeInferenceTestCase
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
        yield 'frontend' => [__DIR__ . '/Data/Application/frontend/properties.php'];
        yield 'backend' => [__DIR__ . '/Data/Application/backend/properties.php'];
        yield 'common config' => [__DIR__ . '/Data/Application/common/config/main.php'];
    }

    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [
            __DIR__ . '/../../extension.neon',
            __DIR__ . '/Config/Application/config.neon',
        ]);
    }
}
