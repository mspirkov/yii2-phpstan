<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\ArrayExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ExplicitNullableObjectInstanceRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withImportNames()
    ->withPhpLevel(20)
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
    ])
    ->withPHPStanConfigs([
        __DIR__ . '/phpstan.dist.neon',
    ])
    ->withSkip([
        __DIR__ . '/tests/Config/Data',
        __DIR__ . '/tests/Extensions/Data',
        __DIR__ . '/tests/Extensions/Source',
        __DIR__ . '/tests/Rules/Data',
        __DIR__ . '/tests/Rules/Source',
        // PHP 7.4
        ClosureToArrowFunctionRector::class,
        // Code Quality
        ExplicitNullableObjectInstanceRector::class,
        IssetOnPropertyObjectToPropertyExistsRector::class,
        SimplifyEmptyArrayCheckRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        SortCallLikeNamedArgsRector::class,
        SimplifyIfReturnBoolRector::class,
        ArrayExplicitBoolCompareRector::class,
        // Dead Code
        SimplifyUselessVariableRector::class,
        RemoveNullPropertyInitializationRector::class,
        RemoveUselessParamTagRector::class,
    ]);
