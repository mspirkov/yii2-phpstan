<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

use Closure;
use MSpirkov\Yii2\PHPStan\Config\ApplicationConfigProvider;
use MSpirkov\Yii2\PHPStan\Resolvers\ConfigValueTypeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\di\Instance;

final class ContainerDefinitionAnalyzer
{
    private ApplicationConfigProvider $applicationConfigProvider;

    private ConfigValueTypeResolver $configValueTypeResolver;

    public function __construct(
        ApplicationConfigProvider $applicationConfigProvider,
        ConfigValueTypeResolver $configValueTypeResolver
    ) {
        $this->applicationConfigProvider = $applicationConfigProvider;
        $this->configValueTypeResolver = $configValueTypeResolver;
    }

    public function resolveType(string $id, Scope $scope): ?Type
    {
        if ($this->configValueTypeResolver->resolveClassType($id) !== null) {
            return null;
        }

        $types = [];
        foreach ($this->applicationConfigProvider->findConfigs($scope->getFile()) as $config) {
            $type = $this->resolveNamedType($id, $this->getDefinitions($config), []);
            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return $types === [] ? null : TypeCombinator::union(...$types);
    }

    /**
     * @param mixed $type
     * @param array<mixed> $config
     */
    public function resolveCreateObjectType($type, array $config): ?Type
    {
        if (is_string($type)) {
            return $this->resolveNamedType($type, $this->getDefinitions($config), []);
        }

        if ($this->isCallableDefinition($type)) {
            return $this->resolveCallableType($type);
        }

        $class = is_array($type) ? $type['__class'] ?? $type['class'] ?? null : null;

        return is_string($class) ? $this->resolveNamedType($class, $this->getDefinitions($config), []) : null;
    }

    /**
     * @param array<mixed> $config
     *
     * @return array<mixed>
     */
    private function getDefinitions(array $config): array
    {
        $container = $config['container'] ?? null;
        if (!is_array($container)) {
            return [];
        }

        $definitions = [];
        foreach ($container as $key => $items) {
            if (!in_array($key, ['definitions', 'singletons'], true) || !is_array($items)) {
                continue;
            }

            foreach ($items as $id => $definition) {
                $definitions[$id] = $this->isDefinitionWithParams($definition) ? $definition[0] : $definition;
            }
        }

        return $definitions;
    }

    /**
     * @param mixed $definition
     *
     * @phpstan-assert-if-true array{mixed, array<mixed>} $definition
     */
    private function isDefinitionWithParams($definition): bool
    {
        return is_array($definition)
            && count($definition) === 2
            && array_values($definition) === $definition
            && is_array($definition[1]);
    }

    /**
     * @param array<mixed> $definitions
     * @param array<string, true> $visited
     */
    private function resolveNamedType(string $name, array $definitions, array $visited): ?Type
    {
        $classType = $this->configValueTypeResolver->resolveClassType($name);
        if ($classType !== null || !array_key_exists($name, $definitions)) {
            return $classType;
        }

        return $this->resolveDefinitionType($name, $definitions, $visited);
    }

    /**
     * @param array<mixed> $definitions
     * @param array<string, true> $visited
     */
    private function resolveDefinitionType(string $id, array $definitions, array $visited): ?Type
    {
        $definition = $definitions[$id];
        if ($this->isCallableDefinition($definition)) {
            return $this->resolveCallableType($definition);
        }

        if (is_object($definition) && !$definition instanceof Instance) {
            return $this->configValueTypeResolver->resolveClassType(get_class($definition));
        }

        $class = $this->resolveDefinitionClass($id, $definition);
        if ($class === null || $class === $id || isset($visited[$id])) {
            return null;
        }

        return $this->resolveNamedType($class, $definitions, $visited + [$id => true]);
    }

    /**
     * @param mixed $definition
     */
    private function resolveDefinitionClass(string $id, $definition): ?string
    {
        if (in_array($definition, [null, '', []], true)) {
            return $id;
        }

        if (is_string($definition)) {
            return $definition;
        }

        if ($definition instanceof Instance) {
            return $definition->id;
        }

        if (!is_array($definition)) {
            return null;
        }

        $class = $definition['class'] ?? $definition['__class'] ?? (strpos($id, '\\') === false ? null : $id);

        return is_string($class) ? $class : null;
    }

    /**
     * @param mixed $definition
     */
    private function isCallableDefinition($definition): bool
    {
        return !is_string($definition) && is_callable($definition, true);
    }

    /**
     * @param mixed $callable
     */
    private function resolveCallableType($callable): ?Type
    {
        if ($callable instanceof Closure) {
            return $this->configValueTypeResolver->resolveClosureReturnType($callable);
        }

        if (is_object($callable)) {
            return $this->configValueTypeResolver->resolveMethodReturnType(get_class($callable), '__invoke');
        }

        return is_array($callable) && is_callable($callable, true)
            ? $this->configValueTypeResolver->resolveMethodReturnType(
                is_object($callable[0]) ? get_class($callable[0]) : $callable[0],
                $callable[1]
            )
            : null;
    }
}
