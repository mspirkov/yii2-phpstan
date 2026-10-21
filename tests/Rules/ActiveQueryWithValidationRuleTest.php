<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\ActiveQueryWithValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ActiveQueryWithValidation\Customer;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ActiveQueryWithValidation\Order;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ActiveQueryWithValidation\Tag;

/**
 * @extends AbstractTestCase<ActiveQueryWithValidationRule>
 */
final class ActiveQueryWithValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [self::getDataFilePath('code')],
            [
                [sprintf('Unknown relation "bogus" for %s in with() call.', Customer::class), 57],
                [sprintf('Unknown relation "displayName" for %s in with() call.', Customer::class), 62],
                [sprintf('Unknown relation "bogus" for %s in with() call.', Order::class), 67],
                [sprintf('Unknown relation "bogus" for %s in with() call.', Customer::class), 72],
                [sprintf('Unknown relation "bogus" for %s in joinWith() call.', Customer::class), 78],
                [sprintf('Unknown relation "bogus" for %s in with() call.', Customer::class), 83],
                [sprintf('Unknown relation "bogus" for %s in with() call.', Tag::class), 88],
            ],
        );
    }

    protected static function getRuleClass(): string
    {
        return ActiveQueryWithValidationRule::class;
    }
}
