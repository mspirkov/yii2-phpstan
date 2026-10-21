<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Extensions;

use MSpirkov\Yii2\PHPStan\Analyzers\ExpressionTypeAnalyzer;
use MSpirkov\Yii2\PHPStan\Analyzers\PathAnalyzer;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\base\Application;
use yii\base\InvalidConfigException;
use yii\BaseYii;

final class YiiAppExpressionTypeResolverExtension implements ExpressionTypeResolverExtension
{
    private const PARAMETER = 'mspirkovYii2.yiiAppTypes';

    /** @var list<list<string>> */
    private array $paths = [];

    /** @var list<array{type: ?Type, nullable: bool}> */
    private array $yiiAppTypes = [];

    private PathAnalyzer $pathAnalyzer;

    private ExpressionTypeAnalyzer $expressionTypeAnalyzer;

    /** @var array<string, list<int>> */
    private array $fileTypeIndexes = [];

    /**
     * @param list<array{paths: list<string>, type?: string, nullable?: bool}> $yiiAppTypes
     */
    public function __construct(
        array $yiiAppTypes,
        PathAnalyzer $pathAnalyzer,
        ExpressionTypeAnalyzer $expressionTypeAnalyzer,
        TypeStringResolver $typeStringResolver
    ) {
        foreach ($yiiAppTypes as $yiiAppType) {
            $this->paths[] = $pathAnalyzer->resolveRealPaths($yiiAppType['paths'], self::PARAMETER);
            $this->yiiAppTypes[] = [
                'type' => isset($yiiAppType['type'])
                    ? $this->resolveType($yiiAppType['type'], $typeStringResolver)
                    : null,
                'nullable' => $yiiAppType['nullable'] ?? true,
            ];
        }

        $this->pathAnalyzer = $pathAnalyzer;
        $this->expressionTypeAnalyzer = $expressionTypeAnalyzer;
    }

    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if (
            !$expr instanceof StaticPropertyFetch
            || !$expr->class instanceof Name
            || !$expr->name instanceof VarLikeIdentifier
            || $expr->name->toString() !== 'app'
            || $scope->hasExpressionType($expr)->yes()
        ) {
            return null;
        }

        $className = $scope->resolveName($expr->class);
        if (!$this->expressionTypeAnalyzer->isClassNameOf($className, BaseYii::class)) {
            return null;
        }

        $indexes = $this->findTypeIndexes($scope->getFile());
        if ($indexes === []) {
            return null;
        }

        $declaredType = TypeCombinator::removeNull(
            (new ObjectType($className))->getStaticProperty('app', $scope)->getReadableType()
        );

        $types = [];
        foreach ($indexes as $index) {
            $type = $this->yiiAppTypes[$index]['type'] ?? $declaredType;
            $types[] = $this->yiiAppTypes[$index]['nullable'] ? TypeCombinator::addNull($type) : $type;
        }

        return TypeCombinator::union(...$types);
    }

    private function resolveType(string $typeString, TypeStringResolver $typeStringResolver): Type
    {
        $type = $typeStringResolver->resolve($typeString);
        if (!(new ObjectType(Application::class))->isSuperTypeOf(TypeCombinator::removeNull($type))->yes()) {
            throw new InvalidConfigException(sprintf(
                'Type "%s" of the "%s" parameter is not a type of %s.',
                $typeString,
                self::PARAMETER,
                Application::class
            ));
        }

        if (TypeCombinator::containsNull($type)) {
            throw new InvalidConfigException(sprintf(
                'Type "%s" of the "%s" parameter must not be nullable. Use the "nullable" option instead.',
                $typeString,
                self::PARAMETER
            ));
        }

        return $type;
    }

    /**
     * @return list<int>
     */
    private function findTypeIndexes(string $file): array
    {
        if (!isset($this->fileTypeIndexes[$file])) {
            $this->fileTypeIndexes[$file] = $this->pathAnalyzer->findClosestPathIndexes($file, $this->paths);
        }

        return $this->fileTypeIndexes[$file];
    }
}
