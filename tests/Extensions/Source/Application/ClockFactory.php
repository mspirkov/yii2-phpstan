<?php

declare(strict_types=1);

namespace MSpirkov\Yii2\PHPStan\Tests\Extensions\Source\Application;

class ClockFactory
{
    public function __invoke(): Clock
    {
        return new Clock();
    }

    public static function create(): Clock
    {
        return new Clock();
    }
}
