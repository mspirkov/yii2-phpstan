<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Analyzers;

final class NamespaceAnalyzer
{
    /**
     * @param list<string> $namespaces
     */
    public function findClosestNamespace(string $namespace, array $namespaces): ?string
    {
        $closestNamespace = null;

        foreach ($namespaces as $candidate) {
            if (
                ($namespace === $candidate || strncmp($namespace, $candidate . '\\', strlen($candidate) + 1) === 0)
                && ($closestNamespace === null || strlen($candidate) > strlen($closestNamespace))
            ) {
                $closestNamespace = $candidate;
            }
        }

        return $closestNamespace;
    }
}
