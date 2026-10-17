<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

use Closure;
use MSpirkov\Yii2\PHPStan\Config\ApplicationConfigProvider;
use MSpirkov\Yii2\PHPStan\Resolvers\ConfigValueTypeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class ApplicationConfigAnalyzer
{
    private ApplicationConfigProvider $applicationConfigProvider;

    private ContainerDefinitionAnalyzer $containerDefinitionAnalyzer;

    private ConfigValueTypeResolver $configValueTypeResolver;

    public function __construct(
        ApplicationConfigProvider $applicationConfigProvider,
        ContainerDefinitionAnalyzer $containerDefinitionAnalyzer,
        ConfigValueTypeResolver $configValueTypeResolver
    ) {
        $this->applicationConfigProvider = $applicationConfigProvider;
        $this->containerDefinitionAnalyzer = $containerDefinitionAnalyzer;
        $this->configValueTypeResolver = $configValueTypeResolver;
    }

    public function resolveComponentType(string $id, Scope $scope): ?Type
    {
        return $this->resolveInScope(
            $scope,
            fn(array $config): ?Type => $this->resolveConfigComponentType($id, $config)
        );
    }

    public function resolveComponentTypeAcrossConfigs(string $id): ?Type
    {
        $types = [];
        foreach ($this->applicationConfigProvider->getConfigs() as $config) {
            if (isset($this->getComponents($config)[$id])) {
                $types[] = $this->resolveConfigComponentType($id, $config) ?? new ObjectWithoutClassType();
            }
        }

        return $types === [] ? null : TypeCombinator::union(...$types);
    }

    public function resolveParamsType(Scope $scope): ?Type
    {
        return $this->resolveInScope($scope, function (array $config): ?Type {
            $params = $config['params'] ?? null;

            return is_array($params) ? $this->configValueTypeResolver->resolveValueType($params) : null;
        });
    }

    /**
     * @param callable(array<mixed>): ?Type $resolve
     */
    private function resolveInScope(Scope $scope, callable $resolve): ?Type
    {
        $types = [];
        foreach ($this->applicationConfigProvider->findConfigs($scope->getFile()) as $config) {
            $type = $resolve($config);
            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return $types === [] ? null : TypeCombinator::union(...$types);
    }

    /**
     * @param array<mixed> $config
     */
    private function resolveConfigComponentType(string $id, array $config): ?Type
    {
        $definition = $this->getComponents($config)[$id] ?? null;
        if (is_object($definition) && !$definition instanceof Closure) {
            return $this->configValueTypeResolver->resolveClassType(get_class($definition));
        }

        return $definition === null
            ? null
            : $this->containerDefinitionAnalyzer->resolveCreateObjectType($definition, $config);
    }

    /**
     * @param array<mixed> $config
     *
     * @return array<mixed>
     */
    private function getComponents(array $config): array
    {
        $components = $config['components'] ?? null;

        return is_array($components) ? $components : [];
    }
}
