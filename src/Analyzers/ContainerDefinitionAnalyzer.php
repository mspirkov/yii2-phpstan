<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

use Closure;
use MSpirkov\Yii2\PHPStan\Config\ApplicationConfigProvider;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use ReflectionFunction;
use ReflectionNamedType;
use yii\di\Instance;

final class ContainerDefinitionAnalyzer
{
    private ApplicationConfigProvider $applicationConfigProvider;

    private ReflectionProvider $reflectionProvider;

    public function __construct(
        ApplicationConfigProvider $applicationConfigProvider,
        ReflectionProvider $reflectionProvider
    ) {
        $this->applicationConfigProvider = $applicationConfigProvider;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function resolveType(string $id, Scope $scope): ?Type
    {
        $definitions = $this->getDefinitions($scope);

        return array_key_exists($id, $definitions) ? $this->resolveDefinitionType($id, $definitions, []) : null;
    }

    /**
     * @return array<mixed>
     */
    private function getDefinitions(Scope $scope): array
    {
        $container = $this->applicationConfigProvider->findConfig($scope->getNamespace())['container'] ?? null;
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
    private function resolveDefinitionType(string $id, array $definitions, array $visited): ?Type
    {
        if (!array_key_exists($id, $definitions)) {
            return $this->createObjectType($id);
        }

        $definition = $definitions[$id];
        if ($definition instanceof Closure) {
            return $this->resolveClosureType($definition);
        }

        if (is_object($definition) && !$definition instanceof Instance) {
            return is_callable($definition)
                ? $this->resolveMethodType(get_class($definition), '__invoke')
                : $this->createObjectType(get_class($definition));
        }

        if (is_array($definition) && is_callable($definition, true)) {
            return $this->resolveMethodType(
                is_object($definition[0]) ? get_class($definition[0]) : $definition[0],
                $definition[1]
            );
        }

        $class = $this->resolveDefinitionClass($id, $definition);
        if ($class === null || isset($visited[$id])) {
            return null;
        }

        return $class === $id
            ? $this->createObjectType($id)
            : $this->resolveDefinitionType($class, $definitions, $visited + [$id => true]);
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
     * @param Closure(mixed ...): mixed $closure
     */
    private function resolveClosureType(Closure $closure): ?Type
    {
        $returnType = (new ReflectionFunction($closure))->getReturnType();
        if (!$returnType instanceof ReflectionNamedType) {
            return null;
        }

        $type = $this->createObjectType($returnType->getName());

        return $type !== null && $returnType->allowsNull() ? TypeCombinator::addNull($type) : $type;
    }

    private function resolveMethodType(string $class, string $method): ?Type
    {
        if (!$this->reflectionProvider->hasClass($class)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($class);
        if (!$classReflection->hasNativeMethod($method)) {
            return null;
        }

        $type = ParametersAcceptorSelector::combineAcceptors(
            $classReflection->getNativeMethod($method)->getVariants()
        )->getReturnType();
        $objectType = TypeCombinator::removeNull($type);

        return $objectType->isObject()->yes() && $objectType->getObjectClassNames() !== [] ? $type : null;
    }

    private function createObjectType(string $class): ?Type
    {
        return $this->reflectionProvider->hasClass($class)
            ? new ObjectType($this->reflectionProvider->getClass($class)->getName())
            : null;
    }
}
