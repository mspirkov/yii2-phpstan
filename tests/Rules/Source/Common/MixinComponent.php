<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common;

use yii\base\Component;

/**
 * @mixin TaggableBehavior
 */
final class MixinComponent extends Component
{
    public function behaviors(): array
    {
        return [TaggableBehavior::class];
    }
}
