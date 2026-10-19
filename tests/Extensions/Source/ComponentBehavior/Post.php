<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\ComponentBehavior;

use yii\base\Component;

class Post extends Component
{
    public string $title = '';

    public function publish(): bool
    {
        return true;
    }
}
