<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Type;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/**
 * A component with behaviors attached by `attachBehavior()` / `attachBehaviors()`. Like `Component::__call()` and
 * `Component::__get()`, it falls back to the behaviors, in attachment order, for the methods and properties the
 * component lacks.
 */
final class ComponentWithBehaviorsType extends ObjectType
{
    /** @var array<array-key, Type> */
    private array $behaviors;

    /**
     * @param array<array-key, Type> $behaviors by name in attachment order, int keys for anonymous behaviors
     */
    public function __construct(
        string $className,
        array $behaviors
    ) {
        parent::__construct($className);

        $this->behaviors = $behaviors;
    }

    /**
     * @return array<array-key, Type>
     */
    public function getBehaviors(): array
    {
        return $this->behaviors;
    }

    public function hasMethod(string $methodName): TrinaryLogic
    {
        $hasMethod = parent::hasMethod($methodName);

        return $hasMethod->yes() || $this->findBehaviorWithMethod($methodName) === null
            ? $hasMethod
            : TrinaryLogic::createYes();
    }

    public function getUnresolvedMethodPrototype(
        string $methodName,
        ClassMemberAccessAnswerer $scope
    ): UnresolvedMethodPrototypeReflection {
        $behavior = parent::hasMethod($methodName)->yes() ? null : $this->findBehaviorWithMethod($methodName);

        return $behavior !== null
            ? $behavior->getUnresolvedMethodPrototype($methodName, $scope)
            : parent::getUnresolvedMethodPrototype($methodName, $scope);
    }

    public function hasInstanceProperty(string $propertyName): TrinaryLogic
    {
        $hasProperty = parent::hasInstanceProperty($propertyName);

        return $hasProperty->yes() || $this->findBehaviorWithProperty($propertyName) === null
            ? $hasProperty
            : TrinaryLogic::createYes();
    }

    public function getUnresolvedInstancePropertyPrototype(
        string $propertyName,
        ClassMemberAccessAnswerer $scope
    ): UnresolvedPropertyPrototypeReflection {
        $behavior = parent::hasInstanceProperty($propertyName)->yes()
            ? null
            : $this->findBehaviorWithProperty($propertyName);

        return $behavior !== null
            ? $behavior->getUnresolvedInstancePropertyPrototype($propertyName, $scope)
            : parent::getUnresolvedInstancePropertyPrototype($propertyName, $scope);
    }

    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        $result = parent::isSuperTypeOf($type);
        if (!$result->yes() || $type instanceof CompoundType) {
            return $result;
        }

        return $type instanceof self && $this->hasBehaviorsAttachedTo($type)
            ? $result
            : $result->and(IsSuperTypeOfResult::createMaybe());
    }

    public function equals(Type $type): bool
    {
        if (
            !parent::equals($type)
            || !$type instanceof self
            || array_keys($type->behaviors) !== array_keys($this->behaviors)
        ) {
            return false;
        }

        foreach ($this->behaviors as $name => $behavior) {
            if (!$behavior->equals($type->behaviors[$name])) {
                return false;
            }
        }

        return true;
    }

    public function describe(VerbosityLevel $level): string
    {
        // On every level, the cache one included: ObjectType keys its static method and supertype caches on it
        $behaviors = [];
        foreach ($this->behaviors as $name => $behavior) {
            $behaviors[] = (is_string($name) ? $name . ': ' : '') . $behavior->describe($level);
        }

        return sprintf('%s&behaviors(%s)', parent::describe($level), implode(', ', $behaviors));
    }

    private function findBehaviorWithMethod(string $methodName): ?Type
    {
        foreach ($this->behaviors as $behavior) {
            if ($behavior->hasMethod($methodName)->yes()) {
                return $behavior;
            }
        }

        return null;
    }

    private function findBehaviorWithProperty(string $propertyName): ?Type
    {
        foreach ($this->behaviors as $behavior) {
            if ($behavior->hasInstanceProperty($propertyName)->yes()) {
                return $behavior;
            }
        }

        return null;
    }

    private function hasBehaviorsAttachedTo(self $type): bool
    {
        foreach ($this->behaviors as $name => $behavior) {
            if (!isset($type->behaviors[$name]) || !$behavior->isSuperTypeOf($type->behaviors[$name])->yes()) {
                return false;
            }
        }

        return true;
    }
}
