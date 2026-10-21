<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;

final class BaseObjectPropertyAnalyzer
{
    public function findInstanceProperty(
        ClassReflection $classReflection,
        string $propertyName,
        Scope $scope
    ): ?ExtendedPropertyReflection {
        if ($classReflection->hasInstanceProperty($propertyName)) {
            return $classReflection->getInstanceProperty($propertyName, $scope);
        }

        return null;
    }

    public function hasWritableProperty(
        ClassReflection $classReflection,
        string $propertyName,
        Scope $scope
    ): bool {
        if ($this->findPublicWritableProperty($classReflection, $propertyName, $scope) !== null) {
            return true;
        }

        return $this->hasPropertySetter($classReflection, $propertyName);
    }

    public function findWritableType(
        ClassReflection $classReflection,
        string $propertyName,
        Scope $scope
    ): ?Type {
        $instanceProperty = $this->findPublicWritableProperty($classReflection, $propertyName, $scope);
        if ($instanceProperty !== null) {
            return $instanceProperty->getWritableType();
        }

        if (!$this->hasPropertySetter($classReflection, $propertyName)) {
            return null;
        }

        $parameters = ParametersAcceptorSelector::combineAcceptors(
            $classReflection->getMethod('set' . ucfirst($propertyName), $scope)->getVariants()
        )->getParameters();

        return $parameters === [] ? null : $parameters[0]->getType();
    }

    public function hasReadableAndWritableProperty(
        ClassReflection $classReflection,
        string $propertyName,
        Scope $scope
    ): bool {
        $instanceProperty = $this->findInstanceProperty($classReflection, $propertyName, $scope);
        if ($instanceProperty !== null && $instanceProperty->isReadable() && $instanceProperty->isWritable()) {
            return true;
        }

        return $this->hasPropertyGetter($classReflection, $propertyName)
            && $this->hasPropertySetter($classReflection, $propertyName);
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if ($classReflection->hasInstanceProperty($propertyName)) {
            return true;
        }

        if ($this->hasPropertyGetter($classReflection, $propertyName)) {
            return true;
        }

        return $this->hasPropertySetter($classReflection, $propertyName);
    }

    /**
     * Attribute names coming from free-form config (e.g. model `rules()` or
     * `attributeLabels()` keys) may reference relation properties (`user.name`) or
     * arbitrary expressions (`COALESCE(map_id, 0)`) a custom validator interprets on its
     * own, so existence is only checked for names that could plausibly be a property.
     */
    public function isUnknownAttribute(ClassReflection $classReflection, string $attributeName): bool
    {
        if (!$this->looksLikePropertyName(trim($attributeName))) {
            return false;
        }

        return !$this->hasProperty($classReflection, $attributeName);
    }

    /**
     * Yii assigns config values from outside the object (`Yii::configure()`,
     * `Container::build()`), so a non-public property is set through `__set()`, i.e. its setter.
     */
    private function findPublicWritableProperty(
        ClassReflection $classReflection,
        string $propertyName,
        Scope $scope
    ): ?ExtendedPropertyReflection {
        $instanceProperty = $this->findInstanceProperty($classReflection, $propertyName, $scope);
        if ($instanceProperty === null || !$instanceProperty->isPublic() || !$instanceProperty->isWritable()) {
            return null;
        }

        return $instanceProperty;
    }

    private function looksLikePropertyName(string $name): bool
    {
        return preg_match('/^[A-Za-z_]\w*$/', $name) === 1;
    }

    private function hasPropertySetter(ClassReflection $classReflection, string $propertyName): bool
    {
        $accessorName = ucfirst($propertyName);

        return $classReflection->hasMethod('set' . $accessorName);
    }

    private function hasPropertyGetter(ClassReflection $classReflection, string $propertyName): bool
    {
        $accessorName = ucfirst($propertyName);

        return $classReflection->hasMethod('get' . $accessorName);
    }
}
