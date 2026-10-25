<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\BehaviorAttributesValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Data\BehaviorAttributesValidation\InvalidPost;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Data\BehaviorAttributesValidation\MixinAttributePost;

/**
 * @extends AbstractTestCase<BehaviorAttributesValidationRule>
 */
final class BehaviorAttributesValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [self::getDataFilePath('code')],
            [
                [sprintf('Unknown attribute "createdAt" for model %s.', InvalidPost::class), 106],
                [sprintf('Unknown attribute "updatedAt" for model %s.', InvalidPost::class), 107],
                [sprintf('Unknown attribute "createdBy" for model %s.', InvalidPost::class), 111],
                [sprintf('Unknown attribute "updatedBy" for model %s.', InvalidPost::class), 112],
                [sprintf('Unknown attribute "titel" for model %s.', InvalidPost::class), 116],
                [sprintf('Unknown attribute "alias" for model %s.', InvalidPost::class), 117],
                [sprintf('Unknown attribute "viewsCount" for model %s.', InvalidPost::class), 122],
                [sprintf('Unknown attribute "createdAt" for model %s.', InvalidPost::class), 127],
                [sprintf('Unknown attribute "updatedAt" for model %s.', InvalidPost::class), 128],
                [sprintf('Unknown attribute "createdAt" for model %s.', InvalidPost::class), 133],
                [sprintf('Unknown attribute "updatedAt" for model %s.', InvalidPost::class), 133],
                [sprintf('Unknown attribute "createdBy" for model %s.', InvalidPost::class), 139],
                [sprintf('Unknown attribute "updatedBy" for model %s.', InvalidPost::class), 139],
                [sprintf('Unknown attribute "aliass" for model %s.', InvalidPost::class), 145],
                [sprintf('Unknown attribute "createdAt" for model %s.', InvalidPost::class), 151],
                [sprintf('Unknown attribute "createdAt" for model %s.', InvalidPost::class), 157],
                [sprintf('Unknown attribute "updatedAt" for model %s.', InvalidPost::class), 157],
                [sprintf('Unknown attribute "nickname" for model %s.', MixinAttributePost::class), 181],
            ],
        );
    }

    protected static function getRuleClass(): string
    {
        return BehaviorAttributesValidationRule::class;
    }
}
