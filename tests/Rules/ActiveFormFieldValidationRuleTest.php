<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\ActiveFormFieldValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ActiveFormFieldValidation\ContactModel;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common\MixinAttributeModel;
use yii\base\Model;

/**
 * @extends AbstractTestCase<ActiveFormFieldValidationRule>
 */
final class ActiveFormFieldValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [self::getDataFilePath('code')],
            [
                [sprintf('Attribute "nickname" is not readable and writable or does not exist on model %s.', ContactModel::class), 25],
                [sprintf('Attribute "fullName" is not readable and writable or does not exist on model %s.', ContactModel::class), 30],
                [sprintf('Attribute "status" is not readable and writable or does not exist on model %s.', ContactModel::class), 40],
                [sprintf('Attribute "secret" is not readable and writable or does not exist on model %s.', ContactModel::class), 45],
                [sprintf('Attribute "nickname" is not readable and writable or does not exist on model %s.', MixinAttributeModel::class), 52],
                [sprintf('Attribute "nickname" is not readable and writable or does not exist on model %s.', Model::class), 88],
            ],
        );
    }

    protected static function getRuleClass(): string
    {
        return ActiveFormFieldValidationRule::class;
    }
}
