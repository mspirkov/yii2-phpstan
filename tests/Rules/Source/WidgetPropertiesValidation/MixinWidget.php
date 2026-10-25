<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules\Source\WidgetPropertiesValidation;

use MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common\TaggableBehavior;
use yii\base\Widget;

/**
 * @mixin TaggableBehavior
 */
final class MixinWidget extends Widget
{
    public function behaviors(): array
    {
        return [TaggableBehavior::class];
    }
}
