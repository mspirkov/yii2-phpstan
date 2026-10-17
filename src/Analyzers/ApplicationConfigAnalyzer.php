<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

use Closure;
use MSpirkov\Yii2\PHPStan\Config\ApplicationConfigProvider;
use MSpirkov\Yii2\PHPStan\Resolvers\ConfigValueTypeResolver;
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

    public function resolveComponentType(string $id, ?string $namespace): ?Type
    {
        $definition = $this->getComponents($namespace)[$id] ?? null;
        if (is_object($definition) && !$definition instanceof Closure) {
            return $this->configValueTypeResolver->resolveClassType(get_class($definition));
        }

        return $definition === null
            ? null
            : $this->containerDefinitionAnalyzer->resolveCreateObjectType($definition, $namespace);
    }

    public function resolveComponentTypeAcrossConfigs(string $id): ?Type
    {
        $types = [];
        foreach ($this->applicationConfigProvider->getNamespaces() as $namespace) {
            if (isset($this->getComponents($namespace)[$id])) {
                $types[] = $this->resolveComponentType($id, $namespace) ?? new ObjectWithoutClassType();
            }
        }

        return $types === [] ? null : TypeCombinator::union(...$types);
    }

    public function resolveParamsType(?string $namespace): ?Type
    {
        $params = $this->applicationConfigProvider->findConfig($namespace)['params'] ?? null;

        return is_array($params) ? $this->configValueTypeResolver->resolveValueType($params) : null;
    }

    /**
     * @return array<mixed>
     */
    private function getComponents(?string $namespace): array
    {
        $components = $this->applicationConfigProvider->findConfig($namespace)['components'] ?? null;

        return is_array($components) ? $components : [];
    }
}
