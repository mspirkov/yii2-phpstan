<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\YiiCreateObjectValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common\MixinComponent;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\YiiCreateObjectValidation\CreatableComponent;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\YiiCreateObjectValidation\PlainObject;

/**
 * @extends AbstractTestCase<YiiCreateObjectValidationRule>
 */
final class YiiCreateObjectValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [self::getDataFilePath('code')],
            [
                [sprintf('Unknown option "unknownOption" for object %s.', CreatableComponent::class), 28],
                [sprintf('Object option "limit" for %s must be int, string given.', CreatableComponent::class), 36],
                [sprintf('Object option "__construct()" for %s must be array, string given.', CreatableComponent::class), 62],
                ['Yii::createObject() configuration array must specify "class" or "__class".', 68],
                ['Yii::createObject() configuration option keys must be strings.', 84],
                ['Yii::createObject() configuration option keys must be strings.', 95],
                ['Yii::createObject() configuration option keys must be strings.', 95],
                ['Yii::createObject() configuration array must specify "class" or "__class".', 95],
                ['Yii::createObject() configuration array must specify "class" or "__class".', 102],
                ['Yii::createObject() configuration option keys must be strings.', 109],
                ['Yii::createObject() configuration option keys must be strings.', 109],
                ['Yii::createObject() configuration option keys must be strings.', 109],
                ['Yii::createObject() configuration array must specify "class" or "__class".', 109],
                [sprintf('Unknown option "unknownOption" for object %s.', PlainObject::class), 116],
                [sprintf('Unknown option "nickname" for object %s.', MixinComponent::class), 126],
            ],
        );
    }

    protected static function getRuleClass(): string
    {
        return YiiCreateObjectValidationRule::class;
    }
}
