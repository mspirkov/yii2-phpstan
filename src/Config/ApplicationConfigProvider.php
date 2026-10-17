<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Config;

use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

final class ApplicationConfigProvider implements ResultCacheMetaExtension
{
    /** @var list<array{paths: list<string>, files: list<string>}> */
    private array $applicationConfigs = [];

    /** @var list<string> */
    private array $bootstrapFiles;

    /** @var array<int, array<mixed>> */
    private array $mergedConfigs = [];

    /** @var array<string, array<mixed>> */
    private array $loadedFiles = [];

    /** @var array<string, true> */
    private array $includedFiles = [];

    /** @var array<string, list<int>> */
    private array $fileConfigIndexes = [];

    /**
     * @param list<array{paths: list<string>, files: list<string>}> $applicationConfigs
     * @param list<string> $bootstrapFiles
     */
    public function __construct(array $applicationConfigs, array $bootstrapFiles)
    {
        foreach ($applicationConfigs as $applicationConfig) {
            $paths = [];
            foreach ($applicationConfig['paths'] as $path) {
                $realPath = realpath($path);
                if ($realPath === false) {
                    throw new InvalidConfigException(sprintf(
                        'Path "%s" of the "mspirkovYii2.applicationConfigs" parameter does not exist.',
                        $path
                    ));
                }

                $paths[] = $realPath;
            }

            $this->applicationConfigs[] = ['paths' => $paths, 'files' => $applicationConfig['files']];
        }

        $this->bootstrapFiles = $bootstrapFiles;
    }

    /**
     * @return list<array<mixed>>
     */
    public function findConfigs(string $file): array
    {
        if (!isset($this->fileConfigIndexes[$file])) {
            $this->fileConfigIndexes[$file] = $this->findConfigIndexes($file);
        }

        return array_map(fn(int $index): array => $this->getConfig($index), $this->fileConfigIndexes[$file]);
    }

    /**
     * @return list<array<mixed>>
     */
    public function getConfigs(): array
    {
        return array_map(fn(int $index): array => $this->getConfig($index), array_keys($this->applicationConfigs));
    }

    public function getKey(): string
    {
        return 'mspirkovYii2.applicationConfigs';
    }

    public function getHash(): string
    {
        $this->getConfigs();

        $files = array_keys($this->includedFiles);
        sort($files);

        return sha1(implode("\n", array_map(
            static fn(string $file): string => $file . ':' . sha1_file($file),
            $files
        )));
    }

    /**
     * @return list<int>
     */
    private function findConfigIndexes(string $file): array
    {
        $realPath = realpath($file);
        $file = $realPath === false ? $file : $realPath;

        $indexes = [];
        $longestLength = 0;
        foreach ($this->applicationConfigs as $index => $applicationConfig) {
            foreach ($applicationConfig['paths'] as $path) {
                $length = strlen($path);
                if (
                    $length < $longestLength
                    || ($file !== $path && strncmp($file, $path . DIRECTORY_SEPARATOR, $length + 1) !== 0)
                ) {
                    continue;
                }

                if ($length > $longestLength) {
                    $indexes = [];
                    $longestLength = $length;
                }

                $indexes[$index] = $index;
            }
        }

        return array_values($indexes);
    }

    /**
     * @return array<mixed>
     */
    private function getConfig(int $index): array
    {
        if (!isset($this->mergedConfigs[$index])) {
            $config = [];
            foreach ($this->applicationConfigs[$index]['files'] as $file) {
                $config = ArrayHelper::merge($config, $this->loadFile($file));
            }

            $this->mergedConfigs[$index] = $config;
        }

        return $this->mergedConfigs[$index];
    }

    /**
     * @return array<mixed>
     */
    private function loadFile(string $file): array
    {
        $path = realpath($file);
        if ($path === false || !is_file($path)) {
            throw new InvalidConfigException(sprintf('Application config file "%s" does not exist.', $file));
        }

        if (!isset($this->loadedFiles[$path])) {
            $this->loadBootstrapFiles();

            $includedFiles = get_included_files();
            $config = (static fn(string $file) => require $file)($path);
            if (!is_array($config)) {
                throw new InvalidConfigException(sprintf('Application config file "%s" must return an array.', $file));
            }

            foreach (array_merge([$path], array_diff(get_included_files(), $includedFiles)) as $includedFile) {
                $this->includedFiles[$includedFile] = true;
            }

            $this->loadedFiles[$path] = $config;
        }

        return $this->loadedFiles[$path];
    }

    /**
     * PHPStan runs the bootstrapFiles only once the analysis starts, after the result cache has called
     * getHash(), but the configs may rely on what they define. require_once, as in PHPStan, keeps them
     * from running twice.
     */
    private function loadBootstrapFiles(): void
    {
        foreach ($this->bootstrapFiles as $file) {
            if (!is_file($file)) {
                throw new InvalidConfigException(sprintf('Bootstrap file "%s" does not exist.', $file));
            }

            (static function (string $file): void {
                require_once $file;
            })($file);
        }
    }
}
