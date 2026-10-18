<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Resolvers;

use Closure;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeFinder;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use ReflectionFunction;
use ReflectionNamedType;

final class ConfigValueTypeResolver
{
    private ReflectionProvider $reflectionProvider;

    private ScopeFactory $scopeFactory;

    private Parser $parser;

    /** @var array<int, true> */
    private array $inferringClosures = [];

    public function __construct(ReflectionProvider $reflectionProvider, ScopeFactory $scopeFactory, Parser $parser)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->scopeFactory = $scopeFactory;
        $this->parser = $parser;
    }

    public function resolveClassType(string $class): ?Type
    {
        return $this->reflectionProvider->hasClass($class)
            ? new ObjectType($this->reflectionProvider->getClass($class)->getName())
            : null;
    }

    /**
     * @param Closure(mixed ...): mixed $closure
     */
    public function resolveClosureReturnType(Closure $closure): ?Type
    {
        $reflection = new ReflectionFunction($closure);
        $returnType = $reflection->getReturnType();
        if ($returnType === null) {
            return $this->inferClosureReturnType($closure, $reflection);
        }

        if (!$returnType instanceof ReflectionNamedType) {
            return null;
        }

        $type = $this->resolveClassType($returnType->getName());

        return $type !== null && $returnType->allowsNull() ? TypeCombinator::addNull($type) : $type;
    }

    public function resolveMethodReturnType(string $class, string $method): ?Type
    {
        if (!$this->reflectionProvider->hasClass($class)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($class);
        if (!$classReflection->hasNativeMethod($method)) {
            return null;
        }

        return $this->filterObjectType(ParametersAcceptorSelector::combineAcceptors(
            $classReflection->getNativeMethod($method)->getVariants()
        )->getReturnType());
    }

    /**
     * @param mixed $value
     */
    public function resolveValueType($value): Type
    {
        if (is_array($value)) {
            return $this->resolveArrayType($value);
        }

        if (is_object($value)) {
            return $this->resolveClassType(get_class($value)) ?? new MixedType();
        }

        if (is_string($value)) {
            return new StringType();
        }

        if (is_int($value)) {
            return new IntegerType();
        }

        if (is_float($value)) {
            return new FloatType();
        }

        return is_bool($value) ? new BooleanType() : new MixedType();
    }

    /**
     * @param Closure(mixed ...): mixed $closure
     */
    private function inferClosureReturnType(Closure $closure, ReflectionFunction $reflection): ?Type
    {
        $file = $reflection->getFileName();
        $id = spl_object_id($closure);
        if ($file === false || isset($this->inferringClosures[$id])) {
            return null;
        }

        $nodes = array_values(array_filter(
            (new NodeFinder())->findInstanceOf($this->parser->parseFile($file), Expr::class),
            static fn(Expr $node): bool => $node instanceof FunctionLike
                && $node->getStartLine() === $reflection->getStartLine()
                && $node->getEndLine() === $reflection->getEndLine()
        ));

        if (count($nodes) !== 1) {
            return null;
        }

        $this->inferringClosures[$id] = true;

        try {
            $scope = $this->scopeFactory->create(ScopeContext::create($file));
            $type = ParametersAcceptorSelector::combineAcceptors(
                $scope->getType($nodes[0])->getCallableParametersAcceptors($scope)
            )->getReturnType();
        } finally {
            unset($this->inferringClosures[$id]);
        }

        return $this->filterObjectType($type);
    }

    private function filterObjectType(Type $type): ?Type
    {
        $objectType = TypeCombinator::removeNull($type);

        return $objectType->isObject()->yes() && $objectType->getObjectClassNames() !== [] ? $type : null;
    }

    /**
     * @param array<mixed> $value
     */
    private function resolveArrayType(array $value): Type
    {
        if ($value === []) {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if (array_values($value) === $value) {
            $itemTypes = [];
            foreach ($value as $item) {
                $itemTypes[] = $this->resolveValueType($item);
            }

            return TypeCombinator::intersect(
                new ArrayType(new IntegerType(), TypeCombinator::union(...$itemTypes)),
                new AccessoryArrayListType(),
                new NonEmptyArrayType()
            );
        }

        $builder = ConstantArrayTypeBuilder::createEmpty();
        foreach ($value as $key => $item) {
            $builder->setOffsetValueType(
                is_int($key) ? new ConstantIntegerType($key) : new ConstantStringType($key),
                $this->resolveValueType($item)
            );
        }

        return $builder->getArray();
    }
}
