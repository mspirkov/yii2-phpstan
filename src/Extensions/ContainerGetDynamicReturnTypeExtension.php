<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ContainerDefinitionAnalyzer;
use MSpirkov\Yii2\PHPStan\Resolvers\ExpressionValueResolver;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\di\Container;

final class ContainerGetDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private ContainerDefinitionAnalyzer $containerDefinitionAnalyzer;

    private ExpressionValueResolver $expressionValueResolver;

    public function __construct(
        ContainerDefinitionAnalyzer $containerDefinitionAnalyzer,
        ExpressionValueResolver $expressionValueResolver
    ) {
        $this->containerDefinitionAnalyzer = $containerDefinitionAnalyzer;
        $this->expressionValueResolver = $expressionValueResolver;
    }

    public function getClass(): string
    {
        return Container::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'get';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $idArg = $methodCall->getArgs()[0] ?? null;
        if ($idArg === null) {
            return null;
        }

        $ids = $this->expressionValueResolver->getStringValues($idArg->value, $scope);
        if ($ids === []) {
            return null;
        }

        $types = [];
        foreach ($ids as $id) {
            $type = $this->containerDefinitionAnalyzer->resolveType($id, $scope->getNamespace());
            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }
}
