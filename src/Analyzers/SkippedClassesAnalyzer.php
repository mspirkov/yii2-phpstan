<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

/**
 * @phpstan-type SkippedClasses list<array{class: string, attributes?: list<string>}>
 */
final class SkippedClassesAnalyzer
{
    /**
     * @param SkippedClasses $skippedClasses
     */
    public function isSkippedAttribute(string $className, string $attributeName, array $skippedClasses): bool
    {
        foreach ($skippedClasses as $entry) {
            if (
                $entry['class'] === $className
                && (!isset($entry['attributes']) || in_array($attributeName, $entry['attributes'], true))
            ) {
                return true;
            }
        }

        return false;
    }
}
