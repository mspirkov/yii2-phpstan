<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Config;

use MSpirkov\Yii2\PHPStan\Analyzers\PathAnalyzer;
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

    public function testMissingPath(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Path "missing/path" of the "mspirkovYii2.applicationConfigs" parameter does not exist.');

        $this->createProvider([['paths' => ['missing/path'], 'files' => []]]);
    }

    public function testMissingConfigFile(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Application config file "missing/config.php" does not exist.');

        $this->createProvider([['paths' => [], 'files' => ['missing/config.php']]])->getConfigs();
    }

    public function testConfigFileNotReturningArray(): void
    {
        $file = __DIR__ . '/Data/ApplicationConfigProvider/invalid.php';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf('Application config file "%s" must return an array.', $file));

        $this->createProvider([['paths' => [], 'files' => [$file]]])->getConfigs();
    }

    public function testMissingBootstrapFile(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Bootstrap file "missing/bootstrap.php" does not exist.');

        $this->createProvider(
            [['paths' => [], 'files' => [__DIR__ . '/Data/ApplicationConfigProvider/bootstrapped.php']]],
            ['missing/bootstrap.php']
        )->getConfigs();
    }

    public function testBootstrapFilesLoadedBeforeConfigs(): void
    {
        $directory = __DIR__ . '/Data/ApplicationConfigProvider';
        $provider = $this->createProvider(
            [['paths' => [], 'files' => [$directory . '/bootstrapped.php']]],
            [$directory . '/bootstrap.php']
        );

        self::assertSame([['basePath' => $directory]], $provider->getConfigs());
    }

    public function testFindConfigs(): void
    {
        $directory = __DIR__ . '/Data/ApplicationConfigProvider/paths';
        $provider = $this->createProvider([
            [
                'paths' => [$directory . '/backend', $directory . '/common', $directory . '/frontend-legacy/config.php'],
                'files' => [$directory . '/backend/main.php'],
            ],
            [
                'paths' => [$directory . '/frontend', $directory . '/common'],
                'files' => [$directory . '/frontend/main.php'],
            ],
            [
                'paths' => [$directory . '/frontend/api'],
                'files' => [$directory . '/frontend/api/main.php'],
            ],
        ]);

        self::assertSame([['id' => 'backend']], $provider->findConfigs($directory . '/backend/main.php'));
        self::assertSame([['id' => 'backend'], ['id' => 'frontend']], $provider->findConfigs($directory . '/common/params.php'));
        self::assertSame([['id' => 'frontend']], $provider->findConfigs($directory . '/frontend/main.php'));
        self::assertSame([['id' => 'api']], $provider->findConfigs($directory . '/frontend/api/main.php'));
        self::assertSame([['id' => 'api']], $provider->findConfigs($directory . '/frontend/../frontend/api/main.php'));
        self::assertSame([['id' => 'backend']], $provider->findConfigs($directory . '/frontend-legacy/config.php'));
        self::assertSame([], $provider->findConfigs($directory . '/frontend-legacy/code.php'));
        self::assertSame([['id' => 'frontend']], $provider->findConfigs($directory . '/frontend/missing/code.php'));
    }

    public function testHashTracksIncludedFiles(): void
    {
        file_put_contents($this->directory . '/main.php', "<?php\n\nreturn ['container' => require __DIR__ . '/container.php'];\n");
        file_put_contents($this->directory . '/container.php', "<?php\n\nreturn ['definitions' => []];\n");

        $provider = $this->createProvider([['paths' => [$this->directory], 'files' => [$this->directory . '/main.php']]]);
        $hash = $provider->getHash();

        self::assertSame('mspirkovYii2.applicationConfigs', $provider->getKey());
        self::assertSame([['container' => ['definitions' => []]]], $provider->findConfigs($this->directory . '/main.php'));
        self::assertSame($hash, $provider->getHash());

        file_put_contents($this->directory . '/container.php', "<?php\n\nreturn ['definitions' => ['foo' => 'bar']];\n");

        self::assertNotSame($hash, $provider->getHash());
    }

    /**
     * @param list<array{paths: list<string>, files: list<string>}> $applicationConfigs
     * @param list<string> $bootstrapFiles
     */
    private function createProvider(array $applicationConfigs, array $bootstrapFiles = []): ApplicationConfigProvider
    {
        return new ApplicationConfigProvider($applicationConfigs, $bootstrapFiles, new PathAnalyzer());
    }
}
