<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior;

use yii\base\Behavior;
use yii\base\Component;

/**
 * @extends Behavior<Component>
 */
class Counter extends Behavior
{
    public int $count = 0;

    public function increment(int $step = 1): int
    {
        return $this->count += $step;
    }

    public function reset(): int
    {
        $count = $this->count;
        $this->count = 0;

        return $count;
    }
}
