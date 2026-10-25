<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules;

use MSpirkov\Yii2\PHPStan\Rules\ControllerActionsValidationRule;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ControllerActionsValidation\MixinAction;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ControllerActionsValidation\NotAction;
use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ControllerActionsValidation\ProjectAction;

/**
 * @extends AbstractTestCase<ControllerActionsValidationRule>
 */
final class ControllerActionsValidationRuleTest extends AbstractTestCase
{
    public function testRule(): void
    {
        $notActionClass = NotAction::class;
        $projectActionClass = ProjectAction::class;

        $this->analyse(
            [self::getDataFilePath('code')],
            [
                ['Controller action ID cannot be empty.', 65],
                [sprintf('Controller action class "%s" must be yii\base\Action or its subclass.', $notActionClass), 66],
                [sprintf('Controller action class "%s" must be yii\base\Action or its subclass.', $notActionClass), 67],
                [sprintf('Controller action class "%s" must be yii\base\Action or its subclass.', $notActionClass), 68],
                ['Controller action configuration option keys must be strings.', 69],
                [sprintf('Unknown option "unknown" for action %s.', $projectActionClass), 70],
                [sprintf('Action option "enabled" for %s must be bool, int given.', $projectActionClass), 71],
                [sprintf('Action option "enabled" for %s must be bool, string given.', $projectActionClass), 72],
                [sprintf('Action option "threshold" for %s must be int, string given.', $projectActionClass), 73],
                [sprintf('Unknown option "nickname" for action %s.', MixinAction::class), 87],
            ],
        );
    }

    protected static function getRuleClass(): string
    {
        return ControllerActionsValidationRule::class;
    }
}
