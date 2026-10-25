<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\ModelAttributeHintsValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelAttributeHintsValidation\AttributeNameShapeModel;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelAttributeHintsValidation\ConfigSkippedAttributesModel;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelAttributeHintsValidation\InvalidModel;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelAttributeHintsValidation\MixinAttributeModel;

/**
 * @extends AbstractTestCase<ModelAttributeHintsValidationRule>
 */
final class ModelAttributeHintsValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [self::getDataFilePath('code')],
            [
                [sprintf('Unknown attribute "nickname" for model %s.', InvalidModel::class), 36],
                ['Model attribute hint contains an empty attribute name.', 39],
                [sprintf('Unknown attribute "unknownAndBadType" for model %s.', InvalidModel::class), 40],
                [sprintf('Unknown attribute "nickname" for model %s.', MixinAttributeModel::class), 129],
                [sprintf('Unknown attribute " login " for model %s.', AttributeNameShapeModel::class), 143],
                [sprintf('Unknown attribute " nickname " for model %s.', AttributeNameShapeModel::class), 144],
                ['Model attribute hint contains an empty attribute name.', 155],
                [sprintf('Unknown attribute "nickname" for model %s.', ConfigSkippedAttributesModel::class), 166],
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
        return ModelAttributeHintsValidationRule::class;
    }
}
