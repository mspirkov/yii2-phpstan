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
use PHPStan\Type\Type;

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
            ? $this->applicationConfigAnalyzer->resolveParamsType($scope->getNamespace())
            : $this->applicationConfigAnalyzer->resolveComponentType($name, $scope->getNamespace());

        return $type !== null && $this->yiiAppAnalyzer->isPropertyFetch($expr->var, $scope) ? $type : null;
    }
}
