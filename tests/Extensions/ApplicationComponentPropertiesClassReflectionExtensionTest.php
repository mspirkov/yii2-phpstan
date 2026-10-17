<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions;

use MSpirkov\Yii2\PHPStan\Extensions\ApplicationComponentPropertiesClassReflectionExtension;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\PaypalGateway;
use MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application\StripeGateway;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPStan\Type\VerbosityLevel;
use yii\base\Module;
use yii\web\Application;

final class ApplicationComponentPropertiesClassReflectionExtensionTest extends TypeInferenceTestCase
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
        yield 'console' => [__DIR__ . '/Data/Application/console/properties.php'];
        yield 'unscoped' => [__DIR__ . '/Data/Application/unscoped.php'];
    }

    public function testHasProperty(): void
    {
        $extension = $this->getExtension();
        $application = $this->getReflectionProvider()->getClass(Application::class);

        self::assertTrue($extension->hasProperty($application, 'payments'));
        self::assertFalse($extension->hasProperty($application, 'mailer'));
        self::assertFalse($extension->hasProperty($application, 'undefined'));
        self::assertFalse($extension->hasProperty($this->getReflectionProvider()->getClass(Module::class), 'payments'));
    }

    public function testGetProperty(): void
    {
        $application = $this->getReflectionProvider()->getClass(Application::class);
        $property = $this->getExtension()->getProperty($application, 'payments');
        $type = sprintf('%s|%s', PaypalGateway::class, StripeGateway::class);

        self::assertSame($application, $property->getDeclaringClass());
        self::assertSame($type, $property->getReadableType()->describe(VerbosityLevel::precise()));
        self::assertSame($type, $property->getWritableType()->describe(VerbosityLevel::precise()));
        self::assertTrue($property->isPublic());
        self::assertFalse($property->isPrivate());
        self::assertFalse($property->isStatic());
        self::assertTrue($property->isReadable());
        self::assertFalse($property->isWritable());
        self::assertTrue($property->canChangeTypeAfterAssignment());
        self::assertNull($property->getDocComment());
        self::assertTrue($property->isDeprecated()->no());
        self::assertNull($property->getDeprecatedDescription());
        self::assertTrue($property->isInternal()->no());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [
            __DIR__ . '/../../extension.neon',
            __DIR__ . '/Config/Application/config.neon',
        ]);
    }

    private function getExtension(): ApplicationComponentPropertiesClassReflectionExtension
    {
        return self::getContainer()->getByType(ApplicationComponentPropertiesClassReflectionExtension::class);
    }

    private function getReflectionProvider(): ReflectionProvider
    {
        return self::getContainer()->getByType(ReflectionProvider::class);
    }
}
