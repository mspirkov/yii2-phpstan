<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\ModelRulesValidationRule;
use yii\validators\Validator;
use stdClass;

/**
 * @extends AbstractTestCase<ModelRulesValidationRule>
 */
final class ModelRulesValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $errors = [
            ['Model validation rule must specify validator type at index 1.', 55],
            ['Model validation rule attributes must be a string or array of strings.', 56],
            ['Model validation rule attributes must be strings.', 57],
            ['Model validation rule contains an empty attribute name.', 58],
            ['Model validation rule validator type must be a string or Closure.', 59],
            ['Model validation rule option keys must be strings.', 60],
            ['Unknown option "lenght" for validator yii\validators\StringValidator.', 61],
            ['Validator "filter" requires option "filter".', 62],
            ['Validator "in" requires option "range".', 63],
            ['Validator "match" requires option "pattern".', 64],
            ['Validator "each" requires option "rule".', 65],
            ['Unknown compare validator operator "<>".', 66],
            ['Unknown date validator type "week".', 67],
            ['IP validator cannot disable both IPv4 and IPv6 checks.', 68],
            ['Match validator option "pattern" has an invalid regular expression "[.".', 69],
            ['"in" validator option "range" must be an array, Closure, or Traversable.', 70],
            ['Embedded validation rule must specify validator type at index 0.', 71],
            ['Unknown option "lenght" for validator yii\validators\StringValidator.', 72],
            ['Validator option "on" must contain only scenario names as strings.', 73],
            ['Model validation rule must be an array or a yii\validators\Validator instance.', 74],
            ['Unknown option "lenght" for validator MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelRulesValidation\ProjectSpecificValidator.', 75],
            ['Validator option "ipv4" for yii\validators\IpValidator must be bool, int given.', 143],
            ['Unknown option "current" for validator yii\validators\StringValidator.', 147],
            ['Model validation rule must specify attribute names at index 0.', 188],
            ['Model validation rule attribute names at index 0 cannot be null.', 189],
            ['Model validation rule validator type at index 1 cannot be null.', 190],
            ['Embedded validation rule validator type at index 0 cannot be null.', 191],
            ['Model validation rule contains an empty attribute name.', 192],
            ['Match validator option "pattern" must be a string.', 193],
            ['Validator option "on" must be a string or array of strings.', 194],
            ['Model validation rule must specify attribute names at index 0.', 195],
            ['Model validation rule must specify validator type at index 1.', 195],
            ['Model validation rule must specify validator type at index 1.', 207],
            ['Validator option "max" for yii\validators\StringValidator must be int|null, string given.', 237],
            ['Validator option "integerOnly" for yii\validators\NumberValidator must be bool, string given.', 238],
            ['Unknown option "attributeNames" for validator yii\validators\RequiredValidator.', 239],
            ['Unknown validator "missingValidatorAlias".', 240],
            ['Unknown attribute "nickname" for model MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelRulesValidation\UnknownAttributeModel.', 279],
            ['Unknown attribute "nickname" for model MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelRulesValidation\UnknownAttributeModel.', 280],
            ['Unknown attribute "nickname" for model MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelRulesValidation\MixinAttributeModel.', 323],
            ['Unknown attribute " login " for model MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelRulesValidation\AttributeNameShapeModel.', 337],
            ['Unknown attribute " nickname " for model MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelRulesValidation\AttributeNameShapeModel.', 338],
        ];

        $this->analyse([self::getDataFilePath('code')], $errors);
    }

    /**
     * Covers the defensive fallback in resolveKnownValidatorClass() for a built-in
     * validator alias whose configured class is not a valid yii\validators\Validator,
     * which cannot happen with Yii's own Validator::$builtInValidators map but is
     * guarded against since that map is a mutable public static property.
     */
    public function testBrokenBuiltInValidatorClassIsReportedAsUnknownValidator(): void
    {
        $originalBuiltInValidators = Validator::$builtInValidators;
        Validator::$builtInValidators['brokenBuiltIn'] = stdClass::class;

        try {
            $this->analyse(
                [__DIR__ . '/Data/ModelRulesValidation/brokenBuiltInValidator.php'],
                [
                    ['Unknown validator "brokenBuiltIn".', 14],
                ],
            );
        } finally {
            Validator::$builtInValidators = $originalBuiltInValidators;
        }
    }

    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [
            self::getConfigFilePath('config'),
        ]);
    }

    protected static function getRuleClass(): string
    {
        return ModelRulesValidationRule::class;
    }
}
