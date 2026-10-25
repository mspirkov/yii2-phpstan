<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Rules\Source\ModelRulesValidation;

use yii\base\Behavior;

final class TaggableBehavior extends Behavior
{
    public $tagValues;

    public function getTagNames(): string
    {
        return '';
    }

    public function setTagNames(string $value): void
    {
    }
}
