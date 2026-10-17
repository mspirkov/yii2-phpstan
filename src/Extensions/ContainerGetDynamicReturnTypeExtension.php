<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ContainerDefinitionAnalyzer;
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

    public function __construct(ContainerDefinitionAnalyzer $containerDefinitionAnalyzer)
    {
        $this->containerDefinitionAnalyzer = $containerDefinitionAnalyzer;
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

        $ids = $scope->getType($idArg->value)->getConstantStrings();
        if ($ids === []) {
            return null;
        }

        $types = [];
        foreach ($ids as $id) {
            $type = $this->containerDefinitionAnalyzer->resolveType($id->getValue(), $scope);
            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }
}
