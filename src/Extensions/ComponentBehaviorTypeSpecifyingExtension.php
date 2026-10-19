<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ExpressionTypeAnalyzer;
use MSpirkov\Yii2\PHPStan\Resolvers\ExpressionValueResolver;
use MSpirkov\Yii2\PHPStan\Type\ComponentWithBehaviorsType;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use yii\base\Behavior;
use yii\base\Component;

final class ComponentBehaviorTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    private const METHOD_ATTACH_BEHAVIOR = 'attachBehavior';
    private const METHOD_ATTACH_BEHAVIORS = 'attachBehaviors';
    private const METHOD_DETACH_BEHAVIOR = 'detachBehavior';
    private const METHOD_DETACH_BEHAVIORS = 'detachBehaviors';

    private const METHODS = [
        self::METHOD_ATTACH_BEHAVIOR,
        self::METHOD_ATTACH_BEHAVIORS,
        self::METHOD_DETACH_BEHAVIOR,
        self::METHOD_DETACH_BEHAVIORS,
    ];

    private ExpressionTypeAnalyzer $expressionTypeAnalyzer;

    private ExpressionValueResolver $expressionValueResolver;

    private TypeSpecifier $typeSpecifier;

    public function __construct(
        ExpressionTypeAnalyzer $expressionTypeAnalyzer,
        ExpressionValueResolver $expressionValueResolver
    ) {
        $this->expressionTypeAnalyzer = $expressionTypeAnalyzer;
        $this->expressionValueResolver = $expressionValueResolver;
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }

    public function getClass(): string
    {
        return Component::class;
    }

    public function isMethodSupported(
        MethodReflection $methodReflection,
        MethodCall $node,
        TypeSpecifierContext $context
    ): bool {
        return $context->null() && in_array($methodReflection->getName(), self::METHODS, true);
    }

    public function specifyTypes(
        MethodReflection $methodReflection,
        MethodCall $node,
        Scope $scope,
        TypeSpecifierContext $context
    ): SpecifiedTypes {
        $type = $scope->getType($node->var);
        $newTypes = [];
        foreach (TypeUtils::flattenTypes($type) as $componentType) {
            $behaviors = $this->getAttachedBehaviors($componentType);
            if ($behaviors === null) {
                return new SpecifiedTypes();
            }

            $className = $componentType->getObjectClassNames()[0];
            $behaviors = $this->resolveBehaviorsAfterCall($methodReflection->getName(), $node, $scope, $behaviors);
            $newTypes[] = $behaviors === []
                ? new ObjectType($className)
                : new ComponentWithBehaviorsType($className, $behaviors);
        }

        $newType = TypeCombinator::union(...$newTypes);
        if ($newType->equals($type)) {
            return new SpecifiedTypes();
        }

        return $this->typeSpecifier
            ->create($node->var, $newType, TypeSpecifierContext::createTruthy(), $scope)
            ->setAlwaysOverwriteTypes();
    }

    /**
     * Only a plain component class is narrowed: `$this`, generic and intersection types would lose their meaning.
     *
     * @return array<array-key, Type>|null
     */
    private function getAttachedBehaviors(Type $type): ?array
    {
        if ($type instanceof ComponentWithBehaviorsType) {
            return $type->getBehaviors();
        }

        $classNames = $type->getObjectClassNames();

        return count($classNames) === 1 && (new ObjectType($classNames[0]))->equals($type) ? [] : null;
    }

    /**
     * @param array<array-key, Type> $behaviors
     *
     * @return array<array-key, Type>
     */
    private function resolveBehaviorsAfterCall(
        string $methodName,
        MethodCall $node,
        Scope $scope,
        array $behaviors
    ): array {
        $args = $node->getArgs();
        if ($methodName === self::METHOD_DETACH_BEHAVIORS) {
            return [];
        }

        if ($methodName === self::METHOD_ATTACH_BEHAVIORS) {
            return isset($args[0])
                ? $this->resolveBehaviorsAfterAttachingAll($behaviors, $scope->getType($args[0]->value))
                : $behaviors;
        }

        $name = isset($args[0])
            ? $this->expressionValueResolver->getSingleStringValue($args[0]->value, $scope)
            : null;

        if ($name === null) {
            return $behaviors;
        }

        if ($methodName === self::METHOD_DETACH_BEHAVIOR) {
            unset($behaviors[$name]);

            return $behaviors;
        }

        return isset($args[1])
            ? $this->resolveBehaviorsAfterAttaching($behaviors, $name, $scope->getType($args[1]->value))
            : $behaviors;
    }

    /**
     * @param array<array-key, Type> $behaviors
     *
     * @return array<array-key, Type>
     */
    private function resolveBehaviorsAfterAttachingAll(array $behaviors, Type $definitionsType): array
    {
        $constantArrays = $definitionsType->getConstantArrays();
        if (count($constantArrays) !== 1) {
            return $behaviors;
        }

        $definitionTypes = $constantArrays[0]->getValueTypes();
        foreach ($constantArrays[0]->getKeyTypes() as $i => $keyType) {
            if ($constantArrays[0]->isOptionalKey($i)) {
                continue;
            }

            $name = $keyType->getValue();
            $behaviors = $this->resolveBehaviorsAfterAttaching(
                $behaviors,
                is_string($name) ? $name : null,
                $definitionTypes[$i]
            );
        }

        return $behaviors;
    }

    /**
     * @param array<array-key, Type> $behaviors
     *
     * @return array<array-key, Type>
     */
    private function resolveBehaviorsAfterAttaching(array $behaviors, ?string $name, Type $definitionType): array
    {
        $behaviorType = $this->expressionTypeAnalyzer->resolveYiiCreateObjectType($definitionType, Behavior::class);
        if ($name === null) {
            if ($behaviorType !== null) {
                $behaviors[] = $behaviorType;
            }

            return $behaviors;
        }

        if ($behaviorType === null) {
            unset($behaviors[$name]);
        } else {
            $behaviors[$name] = $behaviorType;
        }

        return $behaviors;
    }
}
