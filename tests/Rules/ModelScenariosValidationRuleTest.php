<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\ModelScenariosValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Data\ModelScenariosValidation\InvalidModel;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Data\ModelScenariosValidation\MixinAttributeModel;

/**
 * @extends AbstractTestCase<ModelScenariosValidationRule>
 */
final class ModelScenariosValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [self::getDataFilePath('code')],
            [
                ['Model scenario name cannot be empty.', 40],
                [sprintf('Unknown attribute "nickname" for model %s.', InvalidModel::class), 41],
                [sprintf('Unknown attribute "missingRole" for model %s.', InvalidModel::class), 42],
                ['Model scenario attribute name cannot be empty.', 43],
                ['Model scenario attributes must be strings.', 44],
                ['Model scenario attributes must be an array of strings.', 45],
                [sprintf('Unknown attribute "nickname" for model %s.', MixinAttributeModel::class), 66],
            ],
        );
    }

    protected static function getRuleClass(): string
    {
        return ModelScenariosValidationRule::class;
    }
}
