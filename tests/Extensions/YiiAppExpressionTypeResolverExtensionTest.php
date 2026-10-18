<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ExpressionTypeAnalyzer;
use MSpirkov\Yii2\PHPStan\Analyzers\PathAnalyzer;
use MSpirkov\Yii2\PHPStan\Extensions\YiiAppExpressionTypeResolverExtension;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Testing\TypeInferenceTestCase;
use yii\base\InvalidConfigException;

final class YiiAppExpressionTypeResolverExtensionTest extends TypeInferenceTestCase
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
        yield 'web' => [__DIR__ . '/Data/YiiApp/web/code.php'];
        yield 'web api' => [__DIR__ . '/Data/YiiApp/web/api/code.php'];
        yield 'web legacy' => [__DIR__ . '/Data/YiiApp/web/legacy/code.php'];
        yield 'untyped' => [__DIR__ . '/Data/YiiApp/untyped/code.php'];
        yield 'console' => [__DIR__ . '/Data/YiiApp/console/code.php'];
        yield 'shared' => [__DIR__ . '/Data/YiiApp/shared/code.php'];
        yield 'unscoped' => [__DIR__ . '/Data/YiiApp/unscoped.php'];
    }

    /**
     * @dataProvider dataInvalidTypes
     */
    public function testInvalidType(string $type): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Type "%s" of the "mspirkovYii2.yiiAppTypes" parameter is not a type of yii\base\Application.',
            $type
        ));

        $container = self::getContainer();
        new YiiAppExpressionTypeResolverExtension(
            [['paths' => [], 'type' => $type]],
            new PathAnalyzer(),
            $container->getByType(ExpressionTypeAnalyzer::class),
            $container->getByType(TypeStringResolver::class)
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function dataInvalidTypes(): iterable
    {
        yield 'scalar' => ['int'];
        yield 'unknown class' => ['app\components\MissingApplication'];
        yield 'nullable' => ['yii\web\Application|null'];
    }

    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [
            __DIR__ . '/../../extension.neon',
            __DIR__ . '/Config/YiiApp/config.neon',
        ]);
    }
}
