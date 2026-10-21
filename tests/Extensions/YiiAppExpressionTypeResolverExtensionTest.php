<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ExpressionTypeAnalyzer;
use MSpirkov\Yii2\PHPStan\Analyzers\PathAnalyzer;
use MSpirkov\Yii2\PHPStan\Extensions\YiiAppExpressionTypeResolverExtension;
use PHPStan\PhpDoc\TypeStringResolver;
use yii\base\InvalidConfigException;

final class YiiAppExpressionTypeResolverExtensionTest extends AbstractTestCase
{
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

        $this->createExtension($type);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function dataInvalidTypes(): iterable
    {
        yield 'scalar' => ['int'];
        yield 'nullable scalar' => ['int|null'];
        yield 'unknown class' => ['app\components\MissingApplication'];
    }

    /**
     * @dataProvider dataNullableTypes
     */
    public function testNullableType(string $type): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Type "%s" of the "mspirkovYii2.yiiAppTypes" parameter must not be nullable. Use the "nullable" option instead.',
            $type
        ));

        $this->createExtension($type);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function dataNullableTypes(): iterable
    {
        yield 'union with null' => ['yii\web\Application|null'];
        yield 'nullable shorthand' => ['?yii\web\Application'];
    }

    protected static function getAdditionalConfigFile(): string
    {
        return __DIR__ . '/Config/YiiApp/config.neon';
    }

    private function createExtension(string $type): YiiAppExpressionTypeResolverExtension
    {
        $container = self::getContainer();

        return new YiiAppExpressionTypeResolverExtension(
            [['paths' => [], 'type' => $type]],
            new PathAnalyzer(),
            $container->getByType(ExpressionTypeAnalyzer::class),
            $container->getByType(TypeStringResolver::class)
        );
    }
}
