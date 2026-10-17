<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Config;

use MSpirkov\Yii2\PHPStan\Analyzers\NamespaceAnalyzer;
use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

final class ApplicationConfigProvider implements ResultCacheMetaExtension
{
    private const NAMESPACE_PATTERN = '/^[A-Za-z_\x80-\xff][\w\x80-\xff]*(?:\\\\[A-Za-z_\x80-\xff][\w\x80-\xff]*)*$/';

    /** @var array<string, list<string>> */
    private array $applicationConfigs = [];

    /** @var list<string> */
    private array $bootstrapFiles;

    private NamespaceAnalyzer $namespaceAnalyzer;

    /** @var array<string, array<mixed>> */
    private array $mergedConfigs = [];

    /** @var array<string, array<mixed>> */
    private array $loadedFiles = [];

    /** @var array<string, true> */
    private array $includedFiles = [];

    /**
     * @param array<string, list<string>> $applicationConfigs
     * @param list<string> $bootstrapFiles
     */
    public function __construct(
        array $applicationConfigs,
        array $bootstrapFiles,
        NamespaceAnalyzer $namespaceAnalyzer
    ) {
        foreach ($applicationConfigs as $namespace => $files) {
            $normalizedNamespace = trim($namespace, '\\');
            if (preg_match(self::NAMESPACE_PATTERN, $normalizedNamespace) !== 1) {
                throw new InvalidConfigException(sprintf(
                    'Key "%s" of the "mspirkovYii2.applicationConfigs" parameter is not a namespace. Use the namespace of the code the configs apply to, e.g. "app\frontend".',
                    $namespace
                ));
            }

            $this->applicationConfigs[$normalizedNamespace] = $files;
        }

        $this->bootstrapFiles = $bootstrapFiles;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }

    /**
     * @return array<mixed>|null
     */
    public function findConfig(?string $namespace): ?array
    {
        if ($namespace === null) {
            return null;
        }

        $configuredNamespace = $this->namespaceAnalyzer->findClosestNamespace($namespace, $this->getNamespaces());

        return $configuredNamespace === null ? null : $this->getConfig($configuredNamespace);
    }

    /**
     * @return list<string>
     */
    public function getNamespaces(): array
    {
        return array_keys($this->applicationConfigs);
    }

    public function getKey(): string
    {
        return 'mspirkovYii2.applicationConfigs';
    }

    public function getHash(): string
    {
        foreach (array_keys($this->applicationConfigs) as $namespace) {
            $this->getConfig($namespace);
        }

        $files = array_keys($this->includedFiles);
        sort($files);

        return sha1(implode("\n", array_map(
            static fn(string $file): string => $file . ':' . sha1_file($file),
            $files
        )));
    }

    /**
     * @return array<mixed>
     */
    private function getConfig(string $namespace): array
    {
        if (!isset($this->mergedConfigs[$namespace])) {
            $config = [];
            foreach ($this->applicationConfigs[$namespace] as $file) {
                $config = ArrayHelper::merge($config, $this->loadFile($file));
            }

            $this->mergedConfigs[$namespace] = $config;
        }

        return $this->mergedConfigs[$namespace];
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
