<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ControllerActionsValidation;

use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common\TaggableBehavior;
use yii\base\Action;

/**
 * @mixin TaggableBehavior
 */
final class MixinAction extends Action
{
    public function behaviors(): array
    {
        return [TaggableBehavior::class];
    }
}
