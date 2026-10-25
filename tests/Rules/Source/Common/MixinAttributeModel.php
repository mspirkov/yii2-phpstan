<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules\Source\Common;

use yii\base\Model;

/**
 * @mixin TaggableBehavior
 */
final class MixinAttributeModel extends Model
{
    public function behaviors(): array
    {
        return [TaggableBehavior::class];
    }
}
