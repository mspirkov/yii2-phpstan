<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

use PHPStan\PhpDoc\StubValidator;

final class YiiAppExpressionTypeResolverExtensionStubsTest extends AbstractTestCase
{
    public static function dataFiles(): iterable
    {
        yield 'web api' => [__DIR__ . '/Data/YiiApp/web/api/code.php'];
    }

    public function testStubFilesValidation(): void
    {
        $stubFiles = [__DIR__ . '/Data/YiiApp/stubs/ApiApplication.stub'];

        self::assertSame([], self::getContainer()->getByType(StubValidator::class)->validate($stubFiles, true));
    }

    protected static function getAdditionalConfigFile(): string
    {
        return __DIR__ . '/Config/YiiApp/stubs.neon';
    }
}
