<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ApplicationConfigAnalyzer;
use MSpirkov\Yii2\PHPStan\Analyzers\YiiAppAnalyzer;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class ApplicationPropertyExpressionTypeResolverExtension implements ExpressionTypeResolverExtension
{
    private ApplicationConfigAnalyzer $applicationConfigAnalyzer;

    private YiiAppAnalyzer $yiiAppAnalyzer;

    public function __construct(ApplicationConfigAnalyzer $applicationConfigAnalyzer, YiiAppAnalyzer $yiiAppAnalyzer)
    {
        $this->applicationConfigAnalyzer = $applicationConfigAnalyzer;
        $this->yiiAppAnalyzer = $yiiAppAnalyzer;
    }

    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if (
            !$expr instanceof PropertyFetch
            || !$expr->name instanceof Identifier
            || $scope->hasExpressionType($expr)->yes()
        ) {
            return null;
        }

        $name = $expr->name->toString();
        $type = $name === 'params'
            ? $this->applicationConfigAnalyzer->resolveParamsType($scope)
            : $this->applicationConfigAnalyzer->resolveComponentType($name, $scope);
        if ($type === null || !$this->yiiAppAnalyzer->isPropertyFetch($expr->var, $scope)) {
            return null;
        }

        return $this->narrowDeclaredType($type, $expr->var, $name, $scope);
    }

    /**
     * Keeps what the declared property type knows beyond the configured class, e.g. the identity class of
     * `yii\web\User<TIdentity>` from `yiiAppTypes`. A configured class unrelated to the declared type wins.
     */
    private function narrowDeclaredType(Type $type, Expr $var, string $name, Scope $scope): Type
    {
        $property = $scope->getInstancePropertyReflection(TypeCombinator::removeNull($scope->getType($var)), $name);
        $intersection = TypeCombinator::intersect(
            $type,
            $property !== null ? $property->getReadableType() : new MixedType()
        );

        return $intersection instanceof NeverType ? $type : $intersection;
    }
}
