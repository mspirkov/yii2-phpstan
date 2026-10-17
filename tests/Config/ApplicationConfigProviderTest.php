<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Config;

use MSpirkov\Yii2\PHPStan\Analyzers\NamespaceAnalyzer;
use MSpirkov\Yii2\PHPStan\Config\ApplicationConfigProvider;
use PHPUnit\Framework\TestCase;
use yii\base\InvalidConfigException;

final class ApplicationConfigProviderTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/' . uniqid('mspirkov-yii2-phpstan-', true);
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        $files = glob($this->directory . '/*.php');
        foreach ($files === false ? [] : $files as $file) {
            unlink($file);
        }

        rmdir($this->directory);
    }

    /**
     * @dataProvider dataInvalidNamespaces
     */
    public function testKeyNotNamespace(string $namespace): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Key "%s" of the "mspirkovYii2.applicationConfigs" parameter is not a namespace.',
            $namespace
        ));

        $this->createProvider([$namespace => []]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function dataInvalidNamespaces(): iterable
    {
        yield 'directory' => ['frontend/controllers'];
        yield 'absolute path' => ['/app/frontend'];
        yield 'wildcard' => ['app\frontend\*'];
        yield 'leading digit' => ['1frontend'];
        yield 'empty segment' => ['app\\\\frontend'];
        yield 'global namespace' => ['\\'];
    }

    public function testMissingConfigFile(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Application config file "missing/config.php" does not exist.');

        $this->createProvider(['app' => ['missing/config.php']])->findConfig('app\controllers');
    }

    public function testConfigFileNotReturningArray(): void
    {
        $file = __DIR__ . '/Data/ApplicationConfigProvider/invalid.php';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf('Application config file "%s" must return an array.', $file));

        $this->createProvider(['app' => [$file]])->findConfig('app');
    }

    public function testHashTracksIncludedFiles(): void
    {
        file_put_contents($this->directory . '/main.php', "<?php\n\nreturn ['container' => require __DIR__ . '/container.php'];\n");
        file_put_contents($this->directory . '/container.php', "<?php\n\nreturn ['definitions' => []];\n");

        $provider = $this->createProvider(['app' => [$this->directory . '/main.php']]);
        $hash = $provider->getHash();

        self::assertSame('mspirkovYii2.applicationConfigs', $provider->getKey());
        self::assertSame(['container' => ['definitions' => []]], $provider->findConfig('app'));
        self::assertSame($hash, $provider->getHash());

        file_put_contents($this->directory . '/container.php', "<?php\n\nreturn ['definitions' => ['foo' => 'bar']];\n");

        self::assertNotSame($hash, $provider->getHash());
    }

    /**
     * @param array<string, list<string>> $applicationConfigs
     */
    private function createProvider(array $applicationConfigs): ApplicationConfigProvider
    {
        return new ApplicationConfigProvider($applicationConfigs, new NamespaceAnalyzer());
    }
}
