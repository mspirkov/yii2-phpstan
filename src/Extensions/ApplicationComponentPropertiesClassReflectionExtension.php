<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ApplicationConfigAnalyzer;
use MSpirkov\Yii2\PHPStan\Reflection\ApplicationComponentPropertyReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\ObjectWithoutClassType;
use yii\base\Application;

final class ApplicationComponentPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    private ApplicationConfigAnalyzer $applicationConfigAnalyzer;

    public function __construct(ApplicationConfigAnalyzer $applicationConfigAnalyzer)
    {
        $this->applicationConfigAnalyzer = $applicationConfigAnalyzer;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $classReflection->is(Application::class)
            && !$this->hasPropertyTag($classReflection, $propertyName)
            && $this->applicationConfigAnalyzer->resolveComponentTypeAcrossConfigs($propertyName) !== null;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new ApplicationComponentPropertyReflection(
            $classReflection,
            $this->applicationConfigAnalyzer->resolveComponentTypeAcrossConfigs($propertyName)
                ?? new ObjectWithoutClassType()
        );
    }

    private function hasPropertyTag(ClassReflection $classReflection, string $propertyName): bool
    {
        foreach (array_merge([$classReflection], $classReflection->getParents()) as $class) {
            if (array_key_exists($propertyName, $class->getPropertyTags())) {
                return true;
            }
        }

        return false;
    }
}
