<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\BaseObjectInstantiationValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\BaseObjectInstantiationValidation\ConfigProcessingObject;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\BaseObjectInstantiationValidation\PositionalObject;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\BaseObjectInstantiationValidation\SetterObject;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\BaseObjectInstantiationValidation\SimpleObject;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common\MixinComponent;

/**
 * @extends AbstractTestCase<BaseObjectInstantiationValidationRule>
 */
final class BaseObjectInstantiationValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [self::getDataFilePath('code')],
            [
                [sprintf('Unknown option "namee" for object %s.', SimpleObject::class), 41],
                [sprintf('Object option "age" for %s must be int, string given.', SimpleObject::class), 42],
                ['Object configuration option keys must be strings.', 43],
                [sprintf('Unknown option "class" for object %s.', SimpleObject::class), 44],
                [sprintf('Unknown option "statuss" for object %s.', PositionalObject::class), 45],
                [sprintf('Unknown option "namee" for object %s.', ConfigProcessingObject::class), 48],
                [sprintf('Object option "transport" for %s must be array<string, string>|string, int given.', SetterObject::class), 51],
                [sprintf('Unknown option "secret" for object %s.', SetterObject::class), 52],
                [sprintf('Unknown option "nickname" for object %s.', MixinComponent::class), 60],
            ],
        );
    }

    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [
            self::getConfigFilePath('config'),
        ]);
    }

    protected static function getRuleClass(): string
    {
        return BaseObjectInstantiationValidationRule::class;
    }
}
